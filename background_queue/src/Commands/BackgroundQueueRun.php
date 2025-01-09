<?php
namespace Drupal\background_queue\Commands;
use Drush\Commands\DrushCommands;
use Drupal\background_queue\Tasks\RunQueueTask;
use Revolt\EventLoop;
use Amp\Parallel\Worker;
use Amp\Future;


class BackgroundQueueRun extends DrushCommands {
  private $queuesNamesToExecute = [];

  public function __construct(){
    $this->queuesNamesToExecute = $this->getDrupalQueuesNames();
  }
  /**
   * Run the background queue.
   *
   * @command background_queue:run
   * @aliases background_queue:run
   * @usage background_queue:run
   *   Run the background queue.
   */
  public function run($options = [
    'time-limit' => self::REQ, 
    'items-limit' => self::REQ, 
    'lease-time' => self::REQ, 
    ]): void {
    
    $queueOptions = [
      'time-limit' => NULL,
      'items-limit' => NULL,
      'lease-time'  => NULL,
    ];
    foreach ($queueOptions as $k => $v) {
      if (isset($options[$k])) {
        $queueOptions[$k] = $options[$k];
      }
      else {
        unset($queueOptions[$k]);
      }
    }

    $queuesNamesToExecute = $this->queuesNamesToExecute;
    EventLoop::repeat(1, function ($callbackId) use (&$queuesNamesToExecute, $queueOptions): void {
      while (count($queuesNamesToExecute) > 0) { 
        $queueName = array_shift($queuesNamesToExecute);
        EventLoop::defer(function () use ($queueName, $queueOptions, &$queuesNamesToExecute): void {
          $queue = \Drupal::queue($queueName, TRUE);
          if ($queue->numberOfItems() > 0) { //avoid empty worker
            $worker = Worker\submit(new RunQueueTask($queueName, $queueOptions));
            $worker->getFuture()->await();
          }
          array_push($queuesNamesToExecute, $queueName); 
        });
      }
    });
    $this->output()->write('####### Ready to Work! ######');
    EventLoop::run();
  }
  public function getDrupalQueuesNames() {
    $queuesInfos = \Drupal::service('plugin.manager.queue_worker')->getDefinitions();
    return array_keys($queuesInfos);
  }
}