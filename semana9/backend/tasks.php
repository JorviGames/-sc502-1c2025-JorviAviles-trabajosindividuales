<?php

require("db.php");

function createTask($userId, $title,$description, $due_date){
    global $pdo;
    try {
        $sql = "insert into tasks (user_id, title, description, due_date) value (:user_id, :title, :description, :due_date";
        $stmt = $pdo->prepare($sql);

        $stmt -> execute([
            'user_id' => $userId,
            'title'=> $title,
            'description'=> $description,
            'due_date' => $due_date
        ]);
        return $pdo -> lastInsertId();

    }catch(Exception $e){
        echo$e->getMessage();
        return 0;
    }
}

function getTaskByUser($userId){

}

function editTask($userId, $title, $description, $due_date){

}

function deleteTask($userId){

}