<?php

namespace jipier\resque;
use Yii;

class ResqueComponent extends \yii\base\Component
{
    /**
     * @var string Redis server address
     */
    public $server = 'localhost';

    /**
     * @var string Redis port number
     */
    public $port = '6379';

    /**
     * @var int Redis database index
     */
    public $database = 0;

    /**
     * @var string Redis password auth
     */
    public $password = '';


    public $prefix = '';

    /**
     * @var mixed include file in daemon (userul for defining YII_DEBUG, etc), may be string or array
     */
    public $includeFiles = '';

    /**
     * Initializes the connection.
     */
    public function init()
    {
        parent::init();
        require_once Yii::getAlias('@vendor/chrisboulton/php-resque/lib/Resque.php');
        require_once Yii::getAlias('@vendor/chrisboulton/php-resque-scheduler/ResqueScheduler/ResqueScheduler.php');

        \Resque::setBackend($this->server . ':' . $this->port, $this->database, $this->password);
        if ($this->prefix) {
            \Resque::redis()->prefix($this->prefix);
        }

    }

    /**
     * Create a new job and save it to the specified queue.
     *
     * @param string $queue The name of the queue to place the job in.
     * @param string $class The name of the class that contains the code to execute the job.
     * @param array $args Any optional arguments that should be passed when the job is executed.
     *
     * @return string
     */
    public function createJob($queue, $class, $args = array(), $track_status = false)
    {

        return \Resque::enqueue($queue, $class, $args, $track_status);
    }

    /**
     * Create a new scheduled job and save it to the specified queue.
     *
     * @param int $in Second count down to job.
     * @param string $queue The name of the queue to place the job in.
     * @param string $class The name of the class that contains the code to execute the job.
     * @param array $args Any optional arguments that should be passed when the job is executed.
     *
     * @return string
     */
    public function enqueueJobIn($in, $queue, $class, $args = array())
    {
        return \ResqueScheduler::enqueueIn($in, $queue, $class, $args);
    }

    /**
     * Create a new scheduled job and save it to the specified queue.
     *
     * @param timestamp $at UNIX timestamp when job should be executed.
     * @param string $queue The name of the queue to place the job in.
     * @param string $class The name of the class that contains the code to execute the job.
     * @param array $args Any optional arguments that should be passed when the job is executed.
     *
     * @return string
     */
    public function enqueueJobAt($at, $queue, $class, $args = array())
    {

        return \ResqueScheduler::enqueueAt($at, $queue, $class, $args);
    }

    public function removeJob($queue, $class, $args) {
        return \ResqueScheduler::removeDelayed($queue, $class, $args);
    }

    /**
     * Get delayed jobs count
     *
     * @return int
     */
    public function getDelayedJobsCount()
    {
        return (int)\Resque::redis()->zcard('delayed_queue_schedule');
    }

    /**
     * Check job status
     *
     * @param string $token Job token ID
     *
     * @return string Job Status
     */
    public function status($token)
    {
        $status = new Resque_Job_Status($token);
        return $status->get();
    }

    /**
     * Return Redis
     *
     * @return object Redis instance
     */
    public function redis()
    {
        return \Resque::redis();
    }

    /**
     * Get queues
     *
     * @return object Redis instance
     */
    public function getQueues()
    {
        return $this->redis()->zRange('delayed_queue_schedule', 0, -1);
    }

    //    public function getValueByKey($key){
    //        return $this->redis()->get($key);
    //    }
}
