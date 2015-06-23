<?php
/**
 * Created by PhpStorm.
 * User: cwioro
 * Date: 22.06.15
 * Time: 13:57
 */


require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__.'/BitcodinApiTestBaseClass.php';

use bitcodin\Bitcodin;
use bitcodin\Input;
use bitcodin\VideoStreamConfig;
use bitcodin\AudioStreamConfig;
use bitcodin\EncodingProfile;
use bitcodin\ManifestTypes;
use bitcodin\Job;
use bitcodin\HttpInputConfig;
use bitcodin\EncodingProfileConfig;
use bitcodin\JobConfig;
use bitcodin\Output;
use bitcodin\S3OutputConfig;

class JobTest extends BitcodinApiTestBaseClass {

    const URL_FILE = 'http://eu-storage.bitcodin.com/inputs/Sintel.2010.720p.mkv';

    public function testCreateJob()
    {
        $inputConfig = new HttpInputConfig();
        $inputConfig->url = self::URL_FILE;
        $input = Input::create($inputConfig);


        /* CREATE VIDEO STREAM CONFIG */
        $videoStreamConfig = new VideoStreamConfig();
        $videoStreamConfig->bitrate = 1024000;
        $videoStreamConfig->height = 480;
        $videoStreamConfig->width = 202;


        /* CREATE AUDIO STREAM CONFIGS */
        $audioStreamConfig = new AudioStreamConfig();
        $audioStreamConfig->bitrate = 256000;

        $encodingProfileConfig = new EncodingProfileConfig();
        $encodingProfileConfig->name = 'MyApiTestEncodingProfile';
        $encodingProfileConfig->videoStreamConfigs[] = $videoStreamConfig;
        $encodingProfileConfig->audioStreamConfigs[] = $audioStreamConfig;


        /* CREATE ENCODING PROFILE */
        $encodingProfile = EncodingProfile::create($encodingProfileConfig);

        $jobConfig = new JobConfig();
        $jobConfig->encodingProfile = $encodingProfile;
        $jobConfig->input = $input;
        $jobConfig->manifestTypes[] = ManifestTypes::M3U8;

        /* CREATE JOB */
        $job = Job::create($jobConfig);

        $this->assertInstanceOf('bitcodin\Job', $job);
        $this->assertNotNull($job->jobId);
        $this->assertNotEquals($job->status, Job::STATUS_ERROR);
        return $job;
    }


    /**
     * @depends JobTest::testCreateJob
     */
    public function testUpdateJob(Job $job)
    {
        /* WAIT TIL JOB IS FINISHED */
        do{
            $job->update();
            $this->assertNotEquals($job->status, Job::STATUS_ERROR);
            sleep(1);
        } while($job->status != Job::STATUS_FINISHED);

        $this->assertEquals($job->status, Job::STATUS_FINISHED);

        return $job;
    }

    /**
     * @depends JobTest::testUpdateJob
     */
    public function testTransferJob(Job $job)
    {
        $s3Config = $this->getKey('s3');
        $outputConfig = new S3OutputConfig();
        $outputConfig->accessKey = $s3Config->accessKey;
        $outputConfig->secretKey = $s3Config->secretKey;
        $outputConfig->name = $s3Config->name;
        $outputConfig->bucket = $s3Config->bucket;
        $outputConfig->region = $s3Config->region;
        $outputConfig->makePublic = false;

        $output = Output::create($outputConfig);
        /* WAIT TIL JOB IS FINISHED */
        $job->transfer($output);
    }

    public function testGetNoneExistingJob()
    {
        $this->setExpectedException('bitcodin\exceptions\BitcodinResourceNotFoundException');
        Job::get(0);

    }
    public function testListAllJobs()
    {

        /* GET LIST OF JOBS */
        foreach(Job::getListAll() as $job)
        {
            $this->assertNotNull($job->jobId);

            $this->assertTrue(in_array($job->status,
                [Job::STATUS_FINISHED, Job::STATUS_ENQUEUED, Job::STATUS_IN_PROGRESS, Job::STATUS_ERROR]
            ), "Invalid job status: " . $job->status);
        }
    }
}
