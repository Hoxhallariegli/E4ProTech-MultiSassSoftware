<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class MockInput extends ArrayInput {
    private $answers;
    public function __construct(array $parameters, array $answers) {
        parent::__construct($parameters);
        $this->answers = $answers;
    }
    public function read() { return array_shift($this->answers); }
    public function isInteractive() { return true; }
}

function runScaffold($name, $answers) {
    echo "🏗️ Scaffolding $name...\n";
    $output = new BufferedOutput();
    // We can't easily mock QuestionHelper in Artisan::call.
    // Instead, we will use proc_open to send inputs to the real command.
    $descriptorspec = [
        0 => ["pipe", "r"], // stdin
        1 => ["pipe", "w"], // stdout
        2 => ["pipe", "w"]  // stderr
    ];
    $process = proc_open("php artisan new:view $name --api --force", $descriptorspec, $pipes);
    if (is_resource($process)) {
        foreach ($answers as $answer) {
            fwrite($pipes[0], $answer . "\n");
        }
        fclose($pipes[0]);
        echo stream_get_contents($pipes[1]);
        echo stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($process);
    }
}

// 1. Plan
runScaffold('Plan', [
    "name", "0", "n",
    "price", "6", "n",
    "duration_months", "2", "n",
    "max_barbers", "2", "n",
    "max_services", "2", "n",
    "max_shops", "2", "n",
    "active", "7", "n",
    "", "chart-bar"
]);

// 2. Subscription
runScaffold('Subscription', [
    "barber_shop_id", "11", "barber_shops", "name", "n",
    "plan_id", "11", "plans", "name", "n",
    "starts_at", "10", "n",
    "ends_at", "10", "n",
    "status", "12", "trial, active, expired, cancelled", "n",
    "auto_renew", "7", "n",
    "", "clipboard"
]);

// 3. Barber
runScaffold('Barber', [
    "barber_shop_id", "11", "barber_shops", "name", "n",
    "user_id", "11", "users", "name", "y",
    "name", "0", "n",
    "phone", "0", "n",
    "photo", "8", "y",
    "bio", "1", "y",
    "active", "7", "n",
    "", "user"
]);

// 4. Service
runScaffold('Service', [
    "barber_shop_id", "11", "barber_shops", "name", "n",
    "name", "0", "n",
    "description", "1", "y",
    "price", "6", "n",
    "duration_minutes", "2", "n",
    "category", "0", "y",
    "active", "7", "n",
    "", "pencil-square"
]);

// 5. WorkingHour
runScaffold('WorkingHour', [
    "barber_shop_id", "11", "barber_shops", "name", "n",
    "day_of_week", "12", "Monday, Tuesday, Wednesday, Thursday, Friday, Saturday, Sunday", "n",
    "open_time", "10", "n",
    "close_time", "10", "n",
    "is_closed", "7", "n",
    "", "clipboard"
]);

// 6. Customer
runScaffold('Customer', [
    "barber_shop_id", "11", "barber_shops", "name", "n",
    "name", "0", "n",
    "phone", "0", "n",
    "email", "0", "y",
    "photo", "8", "y",
    "total_bookings", "2", "n",
    "no_show_count", "2", "n",
    "blocked_at", "10", "y",
    "", "users"
]);

// 7. NotificationChannel
runScaffold('NotificationChannel', [
    "barber_shop_id", "11", "barber_shops", "name", "n",
    "channel", "12", "sms, whatsapp", "n",
    "enabled", "7", "n",
    "daily_limit", "2", "y",
    "", "bell"
]);

// 8. EventSetting
runScaffold('EventSetting', [
    "barber_shop_id", "11", "barber_shops", "name", "n",
    "realtime_event_id", "11", "realtime_events", "event", "n",
    "reverb_enabled", "7", "n",
    "firebase_enabled", "7", "n",
    "", "bolt"
]);

// 9. MessageTemplate
runScaffold('MessageTemplate', [
    "barber_shop_id", "11", "barber_shops", "name", "n",
    "channel", "12", "sms, whatsapp", "n",
    "type", "12", "reminder, confirmation, welcome", "n",
    "content", "1", "n",
    "", "document"
]);

// 10. Booking
runScaffold('Booking', [
    "barber_shop_id", "11", "barber_shops", "name", "n",
    "barber_id", "11", "barbers", "name", "n",
    "service_id", "11", "services", "name", "n",
    "customer_id", "11", "customers", "name", "n",
    "appointment_at", "10", "n",
    "status", "12", "pending, confirmed, completed, cancelled, no-show", "n",
    "total_price", "6", "n",
    "notes", "1", "y",
    "source", "12", "online, walk-in, phone", "n",
    "", "chart-bar"
]);

// 11. Payment
runScaffold('Payment', [
    "barber_shop_id", "11", "barber_shops", "name", "n",
    "booking_id", "11", "bookings", "id", "n",
    "amount", "6", "n",
    "method", "12", "cash, card", "n",
    "status", "12", "pending, paid, refunded, failed", "n",
    "", "archive-box"
]);

// 12. MessageQueue
runScaffold('MessageQueue', [
    "barber_shop_id", "11", "barber_shops", "name", "n",
    "booking_id", "11", "bookings", "id", "n",
    "channel", "12", "sms, whatsapp", "n",
    "phone_number", "0", "n",
    "message_content", "1", "n",
    "scheduled_at", "10", "n",
    "status", "12", "pending, processing, sent, failed, skipped_limit", "n",
    "retry_count", "2", "n",
    "", "bell"
]);

// 13. MessageLog
runScaffold('MessageLog', [
    "barber_shop_id", "11", "barber_shops", "name", "n",
    "customer_id", "11", "customers", "name", "n",
    "channel", "12", "sms, whatsapp", "n",
    "message", "1", "n",
    "status", "12", "sent, failed", "n",
    "sent_at", "10", "y",
    "", "document"
]);

// 14. DeviceToken
runScaffold('DeviceToken', [
    "barber_shop_id", "11", "barber_shops", "name", "n",
    "user_id", "11", "users", "name", "n",
    "fcm_token", "0", "n",
    "platform", "12", "android, ios", "n",
    "last_used_at", "10", "y",
    "", "bolt"
]);

// 15. Review
runScaffold('Review', [
    "barber_shop_id", "11", "barber_shops", "name", "n",
    "barber_id", "11", "barbers", "name", "n",
    "customer_id", "11", "customers", "name", "n",
    "booking_id", "11", "bookings", "id", "n",
    "rating", "2", "n",
    "comment", "1", "y",
    "", "pencil-square"
]);
