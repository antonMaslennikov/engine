<?php

    namespace tomdom\core\api;

    class jivosite
    {
        const COMMAND_NEW_MESSAGE = 'new_message';
        
        const API_COMMAND_FINISHED = 'chat_finished';
        const API_COMMAND_OFFLINE_M = 'offline_message';
        
        public static $apiUrl = 'https://api.textback.io/api/';
        public static $apiToken = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzY29wZXMiOlsiYWNjb3VudDphZGRfdXNlcnMiLCJhY2NvdW50OmVkaXRfb3duX3Byb2ZpbGUiLCJhY2NvdW50OmVkaXRfdXNlcnMiLCJhY2NvdW50OnJlbW92ZV91c2VycyIsImFjY291bnQ6dmlld191c2VycyIsImFwaXRva2Vuczppc3N1ZSIsImF0dGFjaG1lbnRzOnVwbG9hZCIsImNoYW5uZWw6Y3JlYXRlIiwiY2hhbm5lbDpnZXRfYnlfYWNjb3VudCIsImNoYXQ6Z2V0IiwiY2hhdDppbml0aWF0ZSIsImNoYXQ6bWFya19yZWFkIiwiY2hhdDptYXJrX3VucmVhZCIsImNoYXQ6cmVwbHkiLCJlbmR1c2VyX25vdGlmaWNhdGlvbnM6Z2V0X2luZm8iLCJlbmR1c2VyX25vdGlmaWNhdGlvbnM6Z2V0X3N1YnNjcmlwdGlvbnMiLCJlbmR1c2VyX25vdGlmaWNhdGlvbnM6bWFuZ2Vfd2lkZ2V0cyIsImVuZHVzZXJfbm90aWZpY2F0aW9uczpyZW1vdmVfc3Vic2NyaXB0aW9ucyIsImVuZHVzZXJfbm90aWZpY2F0aW9uczpzZW5kIiwiaW50ZXJhY3RpdmVfY2hhaW5zOm1hbmFnZSIsImludm9pY2VzOnBheSIsIm1lc3NhZ2VUZW1wbGF0ZTpnZXQiLCJtZXNzYWdlVGVtcGxhdGU6bWFuYWdlIiwibm90aWZpY2F0aW9uX3dlYmhvb2tzOmFkZCIsIm5vdGlmaWNhdGlvbl93ZWJob29rczpyZW1vdmUiLCJyZXBvcnRzOnZpZXciLCJzdWJzY3JpcHRpb25zOmFjdGl2YXRlIiwic3Vic2NyaXB0aW9uczpnZXQiLCJ3aWRnZXQ6Z2V0Iiwid2lkZ2V0Om1vZGlmeSJdLCJhY2NvdW50LmlkIjoiYjIxZDNkMzQtMzNhOS00NmJlLWI1M2QtNWZjMzg3N2NlZTIyIiwidXNlci5pZCI6ImVkOGI4OGQ0LTNlN2EtNGFlMy1iMzk2LWM5M2UzZjc3ZTA3ZiIsIm5iZiI6MTUzNjY3NDg4NCwianRpIjoiYzVlZWI5NzAtNjBhZi1jYjlhLTJjNzYtMDE2NWM4ZjVlYzUxIiwiaWF0IjoxNTM2Njc0ODg0LCJleHAiOjE1MzY5MjM5NDQsImlzcyI6Imh0dHBzOi8vaWQudGV4dGJhY2suaW8vYXV0aC8iLCJzdWIiOiJlZDhiODhkNC0zZTdhLTRhZTMtYjM5Ni1jOTNlM2Y3N2UwN2YifQ.h7Za-DvTvVWq4lyn3gFs7mlg1WoziDv4AAnpAVvDEC8';
        
        protected $channel;
        protected $channelId;
        protected $chatId;
        
        public $message_id;
        
        public static function catchWebhook()
        {
            $postData = file_get_contents('php://input');
            
            $postData = '{"event_name":"chat_finished","chat_id":84,"widget_id":"lWrSvDecqo","visitor":{"name":"Р—РѕСЏ","email":"kazachenko.1799@mail.ru","phone":"(999) 829-1550","number":83,"chats_count":2},"chat":{"messages":[{"timestamp":1537692485,"type":"visitor","message":"Р—РґСЂР°РІСЃС‚РІСѓР№С‚Рµ!РєР°Рє СЃРјРѕС‚СЂРµС‚СЊСЃСЏ РІР¶РёРІСѓСЋ СЌС‚Рё С€С‚РѕСЂС‹ РІ РєРѕРјРЅР°С‚Рµ,Рё РїРѕРґРѕР№РґСѓС‚ РѕРЅРё РґР»СЏ РєР°СЂРЅРёР·Р° 322СЃРј"},{"timestamp":1537692507,"agent_id":1186180,"type":"agent","message":"Р”РѕР±СЂС‹Р№ РґРµРЅСЊ!"},{"timestamp":1537692540,"agent_id":1186180,"type":"agent","message":"РќСѓР¶РЅРѕ РѕС„РѕСЂРјРёС‚СЊ Р·Р°РєР°Р· РЅР° С€С‚РѕСЂС‹ Рё РїСЂРёРµС…Р°С‚СЊ Рє РЅР°Рј РІ РѕС„РёСЃ Р»РёР±Рѕ Р·Р°РєР°Р·Р°С‚СЊ РєСѓСЂСЊРµСЂСЃРєСѓСЋ РґРѕСЃС‚Р°РІРєСѓ."}],"rate":null},"agents":[{"id":1186180,"email":"valetomdom@gmail.com","name":"Р’Р°Р»РµСЂРёСЏ"}],"session":{"geoip":{"region_code":"48","country_code":"RU","country":"Russian Federation","region":"Moscow City","city":"Moscow","isp":"","latitude":"55.7522","longitude":"37.6156","organization":"Yota"},"utm":"keyword=low_funnel|campaign=cpc|source=criteo","utm_json":{"keyword":"low_funnel","campaign":"cpc","source":"criteo"},"ip_addr":"94.25.169.111","user_agent":"Mozilla/5.0 (Linux; Android 7.0; HUAWEI VNS-L21 Build/HUAWEIVNS-L21) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.91 Mobile Safari/537.36"},"page":{"url":"https://tomdom.ru/catalog/komplekti-shtor/dimideja-bezhevo-zolotoj.html?utm_source=criteo&utm_campaign=cpc&utm_term=low_funnel","title":"РљСѓРїРёС‚СЊ РєРѕРјРїР»РµРєС‚ С€С‚РѕСЂ В«Р”РёРјРёРґРµСЏ (Р±РµР¶РµРІРѕ-Р·РѕР»РѕС‚РѕР№)В» Р¶РµР»С‚С‹Р№/Р·РѕР»РѕС‚Рѕ, Р±РµР¶РµРІС‹Р№ РїРѕ С†РµРЅРµ 5330 СЂСѓР±. СЃ РґРѕСЃС‚Р°РІРєРѕР№ РїРѕ РњРѕСЃРєРІРµ Рё Р РѕСЃСЃРёРё - РёРЅС‚РµСЂРЅРµС‚-РјР°РіР°Р·РёРЅ В«РўРѕРјР”РѕРјВ»"}}';
            
            $data = json_decode($postData);
            
            //$f = fopen(__DIR__ . DIRECTORY_SEPARATOR . 'jivosite.debug.txt', 'a+');
            //fwrite($f, $postData . "\n\n");
            
            $r = new \stdClass();
            
            $r->command = $data->event_name;
            
            $r->name = $data->visitor->name;
            $r->phone = $data->visitor->phone;
            $r->email = $data->visitor->email;
            $r->client_number = $data->visitor->number;
            
            $r->chat_id = $data->chat_id;
            $r->chat = $data->chat;
            $r->offline_message_id = $data->offline_message_id;
            $r->message = $data->message;
            
            return $r;
        }
    }