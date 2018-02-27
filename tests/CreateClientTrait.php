<?php

use Detrack\DetrackCore\Client\DetrackClient;

trait CreateClientTrait{
  private function createClient(){
      try{
        $dotenv = new Dotenv\Dotenv(__DIR__ . "/..");
        $dotenv->load();
      }catch(Exception $ex){
        throw new RuntimeException(".env file not found. Please refer to .env.example and create one.");
      }
      $apiKey = getenv("DETRACK_TESTING_API_KEY");
      //for some reason is_null does not work here
      if($apiKey==NULL){
        throw new RuntimeException("You need to provide an API Key for testing.
                                    \n Please sign up for an account at detrack.com
                                    \n and supply the API Key in a .env file in the library's root folder.
                                    \n See the .env.example file for details.\n");
      }
      $proxy = getenv("LOCAL_PROXY_PORT");
      $this->client = new DetrackClient($apiKey,$proxy);
  }
}


 ?>
