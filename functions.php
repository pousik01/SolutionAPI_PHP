<?php
    //Получение данных о книжках
    function get_all_posts($connection){
        $books = mysqli_query($connection, "SELECT * FROM `books` WHERE `is_deleted` = 0");
        $bookList = [];
        while($book =mysqli_fetch_assoc($books)){
            $bookList[] = $book;
        }
        return json_encode($bookList);
    }

    function get_book($connection, $id){
        $stmt = mysqli_prepare($connection, "SELECT * FROM `books` WHERE `id` = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if(mysqli_num_rows($result) == 0){
            mysqli_stmt_close($stmt);
            http_response_code(404);
            $res = [
                'status' => false,
                'message' => 'Книга не найдена'
            ];
            return json_encode($res);
        }else{
            $book = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);
            return json_encode($book);
        }
    }

    //Занесение данных
    function post_book($connection, $data){
        $title = $data["title"] ?? '';
        $content = $data["content"] ?? '';
        $user_id = $data["user_id"] ?? 0;
        
        // Проверка обязательных полей
        if(empty($title) || empty($content) || $user_id <= 0){
            http_response_code(400);
            $res = [
                'status' => false,
                'message' => 'Не все обязательные поля заполнены'
            ];
            return json_encode($res);
        }
        
        $stmt = mysqli_prepare($connection, 
            "INSERT INTO `books` (`user_id`, `title`, `content`, `is_deleted`, `deleted_at`, `created_at`, `updated_at`) 
            VALUES (?, ?, ?, '0', NULL, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)");
        
        mysqli_stmt_bind_param($stmt, "iss", $user_id, $title, $content);
        
        if(!mysqli_stmt_execute($stmt)){
            mysqli_stmt_close($stmt);
            http_response_code(500);
            $res = [
                'status' => false,
                'message' => 'Ошибка создания: ' . mysqli_error($connection)
            ];
            return json_encode($res);
        }
        
        $new_id = mysqli_insert_id($connection);
        mysqli_stmt_close($stmt);
        
        http_response_code(201);
        $res = [
            'status' => true,
            'post_id' => $new_id
        ];
        return json_encode($res);
    }

    //Изменение данных
    function patch_book($connection, $data, $id){
        // Проверяем, существует ли книга
        $check_stmt = mysqli_prepare($connection, "SELECT id FROM `books` WHERE `id` = ?");
        mysqli_stmt_bind_param($check_stmt, "i", $id);
        mysqli_stmt_execute($check_stmt);
        mysqli_stmt_store_result($check_stmt);
        
        if(mysqli_stmt_num_rows($check_stmt) == 0){
            mysqli_stmt_close($check_stmt);
            http_response_code(404);
            $res = [
                'status' => false,
                'message' => 'Книга не найдена'
            ];
            return json_encode($res);
        }
        mysqli_stmt_close($check_stmt);
        
        // Подготавливаем данные для обновления (с проверкой наличия полей)
        $title = isset($data["title"]) ? $data["title"] : null;
        $content = isset($data["content"]) ? $data["content"] : null;
        $user_id = isset($data["user_id"]) ? $data["user_id"] : null;
        
        // Создаем динамический SQL-запрос с учетом только переданных полей
        $set_parts = [];
        $params = [];
        $types = '';
        
        if($title !== null){
            $set_parts[] = "`title` = ?";
            $params[] = $title;
            $types .= 's';
        }
        
        if($content !== null){
            $set_parts[] = "`content` = ?";
            $params[] = $content;
            $types .= 's';
        }
        
        if($user_id !== null){
            $set_parts[] = "`user_id` = ?";
            $params[] = $user_id;
            $types .= 'i';
        }
        
        // Если не передано ни одного поля для обновления
        if(empty($set_parts)){
            http_response_code(404);
            $res = [
                'status' => false,
                'message' => 'Не переданы данные для обновления'
            ];
            return json_encode($res);
        }
        
        // Добавляем обновление даты
        $set_parts[] = "`updated_at` = CURRENT_TIMESTAMP";
        
        // Формируем SQL-запрос
        $sql = "UPDATE `books` SET " . implode(', ', $set_parts) . " WHERE `id` = ?";
        $types .= 'i'; // Для id
        $params[] = $id; // Добавляем id в параметры
        
        // Подготавливаем и выполняем запрос
        $stmt = mysqli_prepare($connection, $sql);
        
        // Динамически связываем параметры
        $bind_params = [$stmt, $types];
        foreach($params as $key => $value){
            $bind_params[] = &$params[$key];
        }
        
        call_user_func_array('mysqli_stmt_bind_param', $bind_params);
        
        // Выполняем запрос
        if(!mysqli_stmt_execute($stmt)){
            mysqli_stmt_close($stmt);
            http_response_code(500);
            $res = [
                'status' => false,
                'message' => 'Ошибка обновления: ' . mysqli_error($connection)
            ];
            return json_encode($res);
        }
        
        mysqli_stmt_close($stmt);
        
        http_response_code(200); // Лучше использовать 200 вместо 202
        $res = [
            'status' => true,
            'message' => 'Книга обновлена',
            'updated_id' => $id // Исправлено: возвращаем id обновленной книги
        ];
        return json_encode($res);
    }
    //Удаление данных
    function delete_book($connection, $id){
        // Проверяем, существует ли книга
        $check = mysqli_query($connection, "SELECT id FROM `books` WHERE `id` = '$id'");
        if(mysqli_num_rows($check) == 0){
            http_response_code(404);
            $res = [
                'status' => false,
                'message' => 'Книга не найдена'
            ];
            return json_encode($res);
        }

        //Защита от sql-инъекции + удаление
        $stmt = mysqli_prepare($connection, "DELETE FROM `books` WHERE `id` = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        
        $res = [
            'status' => true,
            'message' => 'book is deleted'
        ];
        return json_encode($res);
}
?>