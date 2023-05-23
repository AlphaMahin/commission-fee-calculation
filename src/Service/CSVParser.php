<?php

require __DIR__ . '/../../vendor/autoload.php';

use Calculation\CommissionTask\Service\User;

$userInstanceHashMap = [];

$csv = ($argc > 1) ? $argv[1] : "";

if (!$csv) {
    echo "No argument of CSV file is supplied";
    die();
}

if (!file_exists($csv)) {
    echo "No CSV file found for resource to read";
    die();
}

$file = fopen($csv, "r");

while (!feof($file)) {
    $data = fgetcsv($file);

    if (!empty($data)) {
        $date = $data[0];
        $userID = $data[1];
        $type = $data[2];
        $operation = $data[3];
        $amount = $data[4];
        $currency = $data[5];

        if (!isset($userInstanceHashMap[$userID])) {
            $userInstanceHashMap[$userID] = new User($userID, $type);
        }

        switch ($operation) {
            case 'withdraw':
                echo $userInstanceHashMap[$userID]->withdraw($amount, $currency, $date) . PHP_EOL;
                break;
            case 'deposit':
                echo $userInstanceHashMap[$userID]->deposit($amount) . PHP_EOL;
                break;
            default:
                break;
        }
    }
}

fclose($file);

// namespace Calculation\CommissionTask\Service;

// Class CSVParser
// {
//     public static function read(string $fileName)
//     {
//         if (!file_exists($fileName)) {
//             throw new Exception("No CSV file found for resource to read");
//             die();
//         }

//         $file = fopen($fileName, "r");
//     }
// }

// try {
//     $csv = ($argc > 1) ? $argv[1] : "";

//     if ($csv) {
//         CSVParser::read($csv);
//     }
// } catch(Exception $e) {
//     echo 'Error message: ' . $e->getMessage();
// }
