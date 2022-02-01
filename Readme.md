# Finnotech-Api Php

Finnotech Api for Get Card Number Name And IBAN Number Name and Validate Person Details By [Finnotech.ir](https://Finnotech.ir)

## Installation

Copy Finnotech.php File in Your Project And Use.

You Can Change Similarity in Check User in Finnoch.php at Line 241 , 242

## Usage

```php
const TOKEN = 'Your Token in Finnotech';
const APPNAME = 'Your App Name in Finnotech';
const APPPASSWORD='App Password in Finnotech';

$finnotech = new Finnotech(TOKEN, APPNAME, APPPASSWORD);

#return IBAN Owner Name
$ibanName= $finnotech->getIBANName('IR****');

#return Card Owner Name
$cardName= $finnotech->getCardName('6037************');

#Send Otp Code For Access To Check Details
$trackId= $finnotech->sendSmsCode('09*********');

#Verify Otp Sms
$token= $finnotech->verifySms('09*********',$otpCode,$nationalCode,$trackId);

#Verify User After verifyCode | arrayOfUserData =[ national_code , trackId , birthDate, firstname , lastName ]
$result = $finnotech->verifyUser ($arrayOfUserData , $token)


```
