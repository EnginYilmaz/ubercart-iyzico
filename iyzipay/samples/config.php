<?php

require_once ($_SERVER['DOCUMENT_ROOT'].'/modules/ubercart/payment/uc_iyzipay/iyzipay/IyzipayBootstrap.php');

IyzipayBootstrap::init();

class Config
{
    public static function options()
    {
        $options = new \Iyzipay\Options();
        $options->setApiKey("sandbox-mz89SNn9RuJIw8ZZdB8eeIZu3Vq4nW9S");
        $options->setSecretKey("sandbox-9sYAYshumBYTnwlEqXJTt2ML5m6jKHDj");
        $options->setBaseUrl("https://sandbox-api.iyzipay.com");
        return $options;
    }
}
