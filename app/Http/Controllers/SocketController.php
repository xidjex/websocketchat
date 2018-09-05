<?php

namespace App;

use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

class Chat implements MessageComponentInterface {


    protected $clients;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $connection) {

        $token = $connection->httpRequest->getUri()->getQuery(); //get user token from URI

        $user = User::where('token',$token)->first();  // find user by token

        if (!$user || $user->state === User::STATUS_BANNED){  // check user status
            $connection->send(json_encode(['type' => 'banned']));
            $connection->close();
        }

        $connection->user = $user; // save current connection to user property

        $this->sendToAll([
            'type' => 'connect-user',
            'user' => $user->toArray()
        ]);

        $this->clients->attach($connection); // Store the new connection to send messages later

        //Send last messages, online users and current user data to logged in user
        $data['usersOnline'] = $this->getConnectedUsers();
        $data['currentUser'] = $user->toArray();
        $data['messages'] = array_reverse(Message::orderBy('created_at', 'DESC')->take(config('chat.startupMessagesCount'))->with('user')->get()->toArray());
        $data['type'] = 'startup';
        if ($user->isAdmin) $data['users'] = User::all();

        $connection->send(json_encode($data));

        echo "New connection! {$user->name} ({$connection->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        /*
         *
         * get text message from client
         *
         * {
         *  "type": "message",
         *  "text": "...."
         * }
         *
         *
         * ban user by id
         *
         * {
         *  "type": "ban",
         *  "id": "10"
         * }
         *
         * unban user by id
         *
         * {
         *  "type": "unban",
         *  "id": "10"
         * }
         *
         */


        $data = json_decode($msg, true);
        echo 'Message: ' . $msg . PHP_EOL;

        if (!$data || empty($data['type'])){ // Break if message data invalid
            return;
        }


        switch($data['type']){
            case 'message':
                // check for 15 sec timeout
                echo($data['text']);
                if ($from->user->mute === User::MUTE_ON || empty($data['text'])){
                    return false;
                }

                if (config('chat.saveInDb')) Message::create(['text' => $data['text'], 'user_id' => $from->user->id]); // Save message to db

                $this->sendToAll([
                    'type'=>'message',
                    'text' => $data['text'],
                    'user'=> $from->user
                ]);

                break;

            case 'ban':

                if ($from->user->isAdmin !== true || empty($data['id'])){
                    return false;
                }

                $this->updateUserData((int)$data['id'], 'state', USER::STATUS_BANNED);

                User::query()->where(['id'=> $data['id']])->update(['state'=>User::STATUS_BANNED]);

                $this->sendToAll([
                    'type'=>'ban',
                    'id'=> $data['id']
                ]);

                break;

            case 'unban':
                if ($from->user->isAdmin !== true || empty($data['id'])){
                    return false;
                }

                $this->updateUserData((int)$data['id'], 'state', USER::STATUS_ACTIVE);

                User::query()->where(['id'=> $data['id']])->update(['state'=>User::STATUS_ACTIVE]);

                $this->sendToAll([
                    'type'=>'unban',
                    'id'=> $data['id']
                ]);
                break;

            case 'mute':
                if ($from->user->isAdmin !== true || empty($data['id'])){
                    return false;
                }

                $this->updateUserData((int)$data['id'], 'mute', USER::MUTE_ON);

                User::query()->where(['id'=> $data['id']])->update(['mute'=>User::MUTE_ON]);

                $this->sendToAll([
                    'type'=>'mute',
                    'id'=> $data['id']
                ]);

                break;

            case 'unmute':
                if ($from->user->isAdmin !== true || empty($data['id'])){
                    return false;
                }

                $this->updateUserData((int)$data['id'], 'mute', USER::MUTE_OFF);

                User::query()->where(['id'=> $data['id']])->update(['mute'=>User::MUTE_OFF]);

                $this->sendToAll([
                    'type'=>'unmute',
                    'id'=> $data['id']
                ]);

                break;
        }


    }

    public function onClose(ConnectionInterface $connection) {
        // The connection is closed, remove it, as we can no longer send it messages

        $user = $connection->user;

        $this->clients->detach($connection);

        $this->sendToAll([
           'type' => 'disconnect-user',
           'user' => $user->toArray()
        ]);

        echo "Connection {$connection->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }

    private function getConnectedUsers() {
        $users = [];
        foreach ($this->clients as $client) {
            $users[]= $client->user->toArray();
        }

        return $users;
    }

    public function sendToAll($data){
        foreach ($this->clients as $client) {
            $client->send(json_encode($data));
        }
    }

    public function updateUserData($id, $attribute, $data) {
        foreach ($this->clients as $client) {
            if ($client->user->id === $id) {
                $client->user->{$attribute} = $data;
                return;
            }
        }
    }
}
