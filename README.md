# [![bitcodin](http://www.bitcodin.com/wp-content/uploads/2014/10/bitcodin-small.gif)](http://www.bitcodin.com)
[![Build Status](https://travis-ci.org/bitmovin/bitcodin-php.svg?branch=master)](https://travis-ci.org/bitmovin/bitcodin-php)
[![Coverage Status](https://coveralls.io/repos/bitmovin/bitcodin-php/badge.svg)](https://coveralls.io/r/bitmovin/bitcodin-php)

The bitcodin API for PHP is a seamless integration with the [bitcodin cloud transcoding system](http://www.bitcodin.com). It enables the generation of MPEG-DASH and HLS content in just some minutes.

Installation
------------

### Composer ###
 
  
To install with composer add the following to your `composer.json` file:
```js
"repositories": 
	[{
      "type": "git",
      "url": "ssh://git@github.com/bitmovin/bitcodin-php.git"
    }],
"require": 
	{
	  "bitmovin/bitcodin-php": "dev-master"
	}
```
Then run `php composer.phar install`

Usage
-----

Before you can start using the api you need to set your API key in the Bitcodin class. Your API key can be found in the settings of your bitcodin user account, as shown in the figure below.

![APIKey](http://www.bitcodin.com/wp-content/uploads/2015/06/api_key.png)

An example how you can set the bitcodin API is shown in the following:

```php
use bitcodin\Bitcodin;

Bitcodin::setApiToken('yourApiKey');
```

Example
-----
The following example demonstrates how to create a simple transcoding job:
```php
use bitcodin\Bitcodin;
use bitcodin\UrlInput;
use bitcodin\EncodingProfile;
use bitcodin\VideoStreamConfig;
use bitcodin\AudioStreamConfig;
use bitcodin\Job;
use bitcodin\FtpInput;
use bitcodin\ManifestTypes;

require_once __DIR__.'/vendor/autoload.php';

/* CONFIGURATION */
Bitcodin::setApiToken('insertYourApiKey'); // Your can find your api key in the settings menu. Your account (right corner) -> Settings -> API

/* CREATE INPUT */
$input = UrlInput::create(['url' => 'http://eu-storage.bitcodin.com/inputs/Sintel.2010.720p.mkv']);

/* CREATE VIDEO STREAM CONFIG */
$videoStreamConfig = new VideoStreamConfig(
    array("bitrate" => 1024000,
          "height"  => 480,
          "width"   => 204));

/* CREATE AUDIO STREAM CONFIGS */
$audioStreamConfig = new AudioStreamConfig(array("bitrate" => 320000));

/* CREATE ENCODING PROFILE */
$encodingProfile = EncodingProfile::create('MyEncodingProfile', array($videoStreamConfig), $audioStreamConfig);

/* CREATE JOB */
$job = Job::create(array('inputId'           => $input,
                         'encodingProfileId' => $encodingProfile,
                         'manifestTypes'     => [ManifestTypes::MPD]
                        )
                    );

/* WAIT TIL JOB IS FINISHED */
do{
    $job->update();
    echo 'Job ['.$job->jobId.']: Status['.$job->status."]\n";
    sleep(1);
} while($job->status != Job::STATUS_FINISHED);

```