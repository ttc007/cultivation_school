<?php
// URL mà bạn muốn gửi yêu cầu POST đến
$url = 'https://sandbox.tonhubapi.com/getTransactions?address=UQAjvkriPSbfOkhDOTMGvWX6UmOqvT9n27I6Mm1wpr5JQTrH';

$url = "https://tonapi.io/v2/blockchain/accounts/UQAjvkriPSbfOkhDOTMGvWX6UmOqvT9n27I6Mm1wpr5JQTrH/transactions";
                $transactions = file_get_contents($url);
                $transactions = json_decode($transactions);
                $transactions = $transactions->transactions;
                echo json_encode($transactions);

?>
