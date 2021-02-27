<?php

    namespace tomdom\core\api;

    class textback
    {
        const CHANELS_TELEGRAM = 'tg';
        const CHANELS_VIBER = 'viber';
        const CHANELS_VK = 'vk';
        const CHANELS_FB = 'facebook';
        const CHANELS_SKYPE = 'skype';
        const CHANELS_WHATSAPP = 'whatsapp';
        
        const COMMAND_NEW_MESSAGE = 'new_message';
        
        const API_COMMAND_CHANELS = 'channels';
        const API_COMMAND_MESSAGES = 'messages';
        const API_CREATE_WEBHOOK = '';
        
        public static $apiUrl = 'https://api.textback.io/api/';
        public static $apiToken = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzY29wZXMiOlsiYWNjb3VudDphZGRfdXNlcnMiLCJhY2NvdW50OmVkaXRfb3duX3Byb2ZpbGUiLCJhY2NvdW50OmVkaXRfdXNlcnMiLCJhY2NvdW50OnJlbW92ZV91c2VycyIsImFjY291bnQ6dmlld191c2VycyIsImFwaXRva2Vuczppc3N1ZSIsImF0dGFjaG1lbnRzOnVwbG9hZCIsImNoYW5uZWw6Y3JlYXRlIiwiY2hhbm5lbDpnZXRfYnlfYWNjb3VudCIsImNoYXQ6Z2V0IiwiY2hhdDppbml0aWF0ZSIsImNoYXQ6bWFya19yZWFkIiwiY2hhdDptYXJrX3VucmVhZCIsImNoYXQ6cmVwbHkiLCJlbmR1c2VyX25vdGlmaWNhdGlvbnM6Z2V0X2luZm8iLCJlbmR1c2VyX25vdGlmaWNhdGlvbnM6Z2V0X3N1YnNjcmlwdGlvbnMiLCJlbmR1c2VyX25vdGlmaWNhdGlvbnM6bWFuZ2Vfd2lkZ2V0cyIsImVuZHVzZXJfbm90aWZpY2F0aW9uczpyZW1vdmVfc3Vic2NyaXB0aW9ucyIsImVuZHVzZXJfbm90aWZpY2F0aW9uczpzZW5kIiwiaW50ZXJhY3RpdmVfY2hhaW5zOm1hbmFnZSIsImludm9pY2VzOnBheSIsIm1lc3NhZ2VUZW1wbGF0ZTpnZXQiLCJtZXNzYWdlVGVtcGxhdGU6bWFuYWdlIiwibm90aWZpY2F0aW9uX3dlYmhvb2tzOmFkZCIsIm5vdGlmaWNhdGlvbl93ZWJob29rczpyZW1vdmUiLCJyZXBvcnRzOnZpZXciLCJzdWJzY3JpcHRpb25zOmFjdGl2YXRlIiwic3Vic2NyaXB0aW9uczpnZXQiLCJ3aWRnZXQ6Z2V0Iiwid2lkZ2V0Om1vZGlmeSJdLCJhY2NvdW50LmlkIjoiYjIxZDNkMzQtMzNhOS00NmJlLWI1M2QtNWZjMzg3N2NlZTIyIiwidXNlci5pZCI6ImVkOGI4OGQ0LTNlN2EtNGFlMy1iMzk2LWM5M2UzZjc3ZTA3ZiIsIm5iZiI6MTUzNjY3NDg4NCwianRpIjoiYzVlZWI5NzAtNjBhZi1jYjlhLTJjNzYtMDE2NWM4ZjVlYzUxIiwiaWF0IjoxNTM2Njc0ODg0LCJleHAiOjE1MzY5MjM5NDQsImlzcyI6Imh0dHBzOi8vaWQudGV4dGJhY2suaW8vYXV0aC8iLCJzdWIiOiJlZDhiODhkNC0zZTdhLTRhZTMtYjM5Ni1jOTNlM2Y3N2UwN2YifQ.h7Za-DvTvVWq4lyn3gFs7mlg1WoziDv4AAnpAVvDEC8';
        
        protected $channel;
        protected $channelId;
        protected $chatId;
        
        public $message_id;
        
        /**
         * Зарегистрировать новый вебхук
         * @param type $url
         */
        public static function createWebhook($url)
        {
            $ch = curl_init();
            
            $data = [
                'url' => $url,
                'events' => ['new_message', 'MessageDeliveryCommand', 'OperatorReadChatCommand', 'EndUserSubscribedOnNotification', 'EndUserUnsubscribedFromNotification', ],
            ];
            
            curl_setopt_array($ch, array(
                CURLOPT_URL            => 'http://tb-notificationsrv-prod.textback.io/notification/webhooks',
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_TIMEOUT        => 600,
                CURLOPT_POST           => TRUE,
                CURLOPT_HTTPHEADER     => ['Content-type: application/json', 'Accept: application/json', 'Authorization: Bearer ' . self::$apiToken],
                CURLOPT_POSTFIELDS     => json_encode($data),
            ));
            
            $h = curl_exec($ch); 
            
            $r = json_decode($h);
            
            printr($h);
            printr($r);
        }
        
        public static function catchWebhook()
        {
            $postData = file_get_contents('php://input');
            $data = json_decode($postData);
            
            //$f = fopen(__DIR__ . DIRECTORY_SEPARATOR . 'textback.debug.txt', 'a+');
            //fwrite($f, $postData . "\n\n");
            
            $r = new \stdClass();
            
            $r->command = $data->name;
            $r->text = $data->substitutions->text;
            $r->channel = $data->substitutions->channel;
            $r->channelId = $data->substitutions->channelId;
            $r->chatId = $data->substitutions->chatId;
            $r->name = $data->substitutions->remoteUsername;
            $r->phone = str_replace('tel:', '', $data->message->remoteContact->phoneNumbers[0]->number);
            $r->email = $data->message->remoteContact->email;
            $r->direction = $data->message->direction;
            
            return $r;
        }
        
        public static function chat() {
            return new self();
        }
        
        public function setChannel($channel, $id) {
            $this->channel = $channel;
            $this->channelId = $id;
            return $this;
        } 
        
        public function setChatId($chat) {
            $this->chatId = $chat;
            return $this;
        } 
        
        public function send($text) {
            
            $text = strip_tags($text);
            
            if (empty($text)) {
                throw new Exception('Текст сообещния не задан', 1);
            }
            
            if (empty($this->chatId)) {
                throw new Exception('Не указан id чата', 1);
            }
            
            if (empty($this->channelId)) {
                throw new Exception('Не указан id канала', 1);
            }
            
            if (empty($this->channel)) {
                throw new Exception('Не указан канал связи', 1);
            }
            
            $data = [
                'chatId' => $this->chatId, // (string, optional): Идентификатор чата, в котором сообщение было отправлено. ,
                'channelId' => $this->channelId, // (string): Идентификатор канала в транспорте ,
                'channel' => $this->channel, // (string): Тип канала = ['tg', 'sms', 'vk'],
                //remoteAddress (string, optional): Идентификатор собеседника ,
                'text' => $text // (string, optional): Текст сообщения ,
            ];
            
            $ch = curl_init();
            
            curl_setopt_array($ch, array(
                CURLOPT_URL            => self::$apiUrl . self::API_COMMAND_MESSAGES,
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_TIMEOUT        => 600,
                CURLOPT_POST           => TRUE,
                CURLOPT_HTTPHEADER     => ['Content-type: application/json', 'Accept: application/json', 'Authorization: Bearer ' . self::$apiToken],
                CURLOPT_POSTFIELDS     => json_encode($data),
            ));
            
            $h = curl_exec($ch); 
            
            $r = json_decode($h);
            
            if ($r->{'$value'}->id) {
                $this->message_id = $r->{'$value'}->id;
            }
            
            return $this;
        }
    }