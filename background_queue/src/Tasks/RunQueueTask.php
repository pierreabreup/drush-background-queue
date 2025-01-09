<?php
namespace Drupal\background_queue\Tasks;
use Amp\Cancellation;
use Amp\Parallel\Worker\Task;
use Amp\Sync\Channel;

class RunQueueTask implements Task {
  private $queueName;
  private $queueOptions;

  public function __construct($queueName, $queueOptions) {
    $this->queueOptions = $queueOptions;
    $this->queueName = $queueName;
  }

  public function run(Channel $channel, Cancellation $cancellation): mixed {
    $queueOptionsAsString = "";
    foreach ($this->queueOptions as $k => $v) {
      $queueOptionsAsString .= " --$k=$v";
    }
    // 'cd ..' means go back to the parent directory where composer.json lives
    shell_exec('cd .. && drush queue:run ' . $this->queueName . $queueOptionsAsString);
    return true;
  }
}