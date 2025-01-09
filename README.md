# drush-background-queue

### This code is a Drupal 10+ module to execute Drupal Queues on background mode

#### How to install
* copy and paste `background_queue` folder into drupal custom modules (E.g. `/opt/drupal/web/modules/custom/`)
* in your drupal root, execute :
* * `composer require amphp/amp:^3.0`
* * `composer require amphp/parallel:^2.3.`
* * `composer require revolt/event-loop:ˆ1.0`

### How to use
in your drupal root, run `drush background_queue:run`.  That command will execute all jobs you've created on `src/Plugin/QueueWorker` inside this module