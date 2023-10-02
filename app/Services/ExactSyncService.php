<?php namespace App\Services;

use Illuminate\Console\Concerns\InteractsWithIO;
use Symfony\Component\Console\Output\ConsoleOutput;

class ExactSyncService {

    /**
        DEFINED DATA MAP (structure):
              THEIR COLUMN => OUR COLUMN
             'id' (int)  => 'id' (int)             
             'attemptId' (int)  => 'attemptId' (int)
             'time' (timestamp) => 'time' (timestamp) 
             'fail' (bool) => 'success' (bool) -> need to switch 1 to 0 and vice versa
             'state' (string) => 'state' (string)
             'ip' (string) => 'ip' (string)
             'userAgent' (string) => 'userAgent' (string)
    */
    
    use InteractsWithIO;

    protected $output;
    protected $connector;

    public function __construct()
    {
        $this->output = new ConsoleOutput();
    }

    /**
     * initial method to connect and init data reading
     */
    public function run() {
        $this->info('Start run.');
        $this->connect();
        $this->readData();
        $this->info('End of run.');
    }

    /**
     * read data, iterate through them and process
     */
    public function readData() {
        $result = $this->fetchResult();
        $this->info('Fetched results, start processing rows.');
        while($row = mysqli_fetch_assoc($result)) {
            $this->processRow($row);
        }
    }

    /**
     * work with specific data row
     * prepare data -> normalize data into our format, sanitize them and write into db
     */
    public function processRow($row) {
        $newArray = [];
        foreach($this->getMapping() as $oldKey => $newKey) {
            $value = $row[$oldKey];
            if($oldKey === 'fail') {
                // convert value
                $value = $value === '1' ? 0 : 1;
            }
            $newArray[$newKey] = $value;
        }
        $this->sanitizeData($newArray);
        $this->writeData($newArray);
    }

    /**
     * query to db to fetch last data from last attempt of fetching data
     */
    public function fetchResult() {
        $lastTime = $this->getLastAttempt();
        $result = mysqli_query($this->connector, "SELECT * FROM wp_wflogins WHERE ctime > ".$lastTime);
        return $result;
    }

    public function connect() {
        $mysqli = mysqli_connect(env('DB_HOST'), env('DB_USERNAME'), env('DB_PASSWORD'), env('DB_DATABASE'));
        $this->connector = $mysqli;
    }

    public function getMapping() {

        return [
            'id' => 'id',
            'hitID' => 'attemptId',
            'ctime' => 'time',
            'fail' => 'success',
            'action' => 'state',
            'IP' => 'ip',
            'UA' => 'userAgent'
        ];

    }

    public function sanitizeData() {

    }

    public function writeData() {


    }


    public function getLastAttempt() {

        return 'getLastAttempt';

    }




}
