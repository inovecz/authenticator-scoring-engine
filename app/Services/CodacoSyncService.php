<?php namespace App\Services;



use Illuminate\Console\Concerns\InteractsWithIO;
use Symfony\Component\Console\Output\ConsoleOutput;

class CodacoSyncService {

    /**
        DEFINED DATA MAP (structure):
              THEIR COLUMN => OUR COLUMN
             'id' (int)  => 'id' (int)             
             'hitID' (int)  => 'attemptId' (int)
             'time' (string) => 'time' (timestamp) -> need to conver to unix timestamp
             'success' (bool) => 'success' (bool)
             'action' (string) => 'state' (string)
             'ip' (string) => 'ip' (string) -> need to convert
             'UA' (string) => 'userAgent' (string)
    */

    use InteractsWithIO;
    protected $connector;

    public function __construct()
    {
        $this->output = new ConsoleOutput();
    }

    /**
     * initial method to connect and init data reading
     */
    public function run() {
        $this->info('Start run of sync with NEXT.');
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
            switch($oldKey) {
                case 'time':
                    $value = $this->convertToUnixTimestamp($value);
                    break;
                case 'ip':
                    $value = $this->convertIP($value);
                    break;
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


    public function convertIP($ip) {
        return $ip;
    }

    public function convertToUnixTimestamp($value) {
       return $value;
    }

    public function sanitizeData() {

    }

    public function writeData() {


    }


    public function getLastAttempt() {

        return 1642873326.406133;

    }




}
