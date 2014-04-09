<?php

class Event extends Model {

    protected $elements = array(
        'EventId' => 'Id',
        'Name' => 'EventTitle',
        'StartDate' => 'EventStartDate',
        'EndDate' => 'EventEndDate',
        //'ImageURL' => '',
        'Address' => 'Address',
        //'Website' => '',
        //'Contact' => '',
        'Description' => 'Summary'
    );

}

?>
