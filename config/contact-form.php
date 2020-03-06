<?php 

use craft\contactform\models\Submission;
use craft\contactform\events\SendEvent;
use craft\contactform\Mailer;
use yii\base\Event;

// ...

$_SESSION["peopleAmount"];

$command = Yii::$app->db->createCommand("SELECT sum(peopleAmount) FROM contactform_submissions");
$sum = $command->queryScalar();
//echo $sum;

if (isset($_COOKIE['inschrijvingen'])) {
    unset($_COOKIE['inschrijvingen']);
    setcookie("inschrijvingen", $sum);
}
else{
    setcookie("inschrijvingen", $sum);
}

Event::on(Submission::class, Submission::EVENT_AFTER_VALIDATE, function(Event $e) {
    /** @var Submission $submission */
    $submission = $e->sender;
   

     // Make sure that `message[Phone]` was filled in
    if (empty($submission->message['phone'])) {
        $submission->addError('message.phone', 'Jouw telefoonnummer mag niet leeg zijn.');
    }
    if (empty($submission->fromName)) {
        $submission->addError('fromName', 'Jouw voornaam mag niet leeg zijn.');
    }
    if (empty($submission->message['lastName'])) {
        $submission->addError('message.lastName', 'Jouw famillienaam mag niet leeg zijn.');
    }
    if (empty($submission->message['people'])) {
        $submission->addError('message.people', 'Aantal mensen mag niet leeg zijn.');
    }
    else{
        $_SESSION["peopleAmount"] = $submission->message['people'];
    }
   
});

Event::on(Mailer::class, Mailer::EVENT_AFTER_SEND, function(SendEvent $e) {

    $row = (new \yii\db\Query())
    ->select(['id'])
    ->from('contactform_submissions')
    ->OrderBy("id DESC")
    ->limit(1)
    ->one();

    if($row){
        Yii::$app->db->createCommand("UPDATE contactform_submissions SET peopleAmount =:peopleAmount WHERE id =:id")
        ->bindValue(':peopleAmount', $_SESSION["peopleAmount"])
        ->bindValue(':id', $row['id'])
        ->execute();
    }

});


?>
