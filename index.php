<?php
/*
Инициализировать сессию (curl_init)
Задать нужные нам опции (curl_setopt)
Выполнить полученный запрос (curl_exec)
Завершить сессию (curl_close)
*/

include "../functions/functions.php";

$url = "https://privatbank.ua/ru"; //URL

/*
    get_web_result() cURL метод для парсинга
*/

echo "<h1>Парсер курса доллара Приват банк</h1><br>";

function get_web_result( $url ){

    $ch = curl_init($url); //Инициализация

    // Опции cURL
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // Возвращает веб-страницу
    curl_setopt($ch, CURLOPT_HEADER, 0); //Не возвращает заголовки
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); //Переходит по редиректам
    curl_setopt($ch, CURLOPT_ENCODING, ""); //Обрабатывает все кодировки
    curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER["HTTP_USER_AGENT"]); //USERAGENT
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120); //Таймаут соединения
    curl_setopt($ch, CURLOPT_TIMEOUT, 120); //Таймаут ответа
    curl_setopt($ch, CURLOPT_MAXREDIRS, 10); //Останавливаться после 10-го редиректа

    //Сохраняем значения
    $content = curl_exec($ch);
    $err     = curl_errno($ch);
    $errmsg  = curl_error($ch);
    $header  = curl_getinfo($ch);

    // Закрываем подключение
    curl_close($ch);

    $header["errno"]    = $err;
    $header["errmsg"]   = $errmsg;
    $header["content"]  = $content;

    return $header;
}

/*
Вырезаем нужный кусок контента
*/

$result = get_web_result($url);

if(($result["errno"] != 0) || ($result["http_code"] != 200)){
    echo $result["errmsg"];
}
else{
    $page = $result["content"];
    
    // Обрезаем текст от начала нужного контента
    $before_pos = strpos($page, "<div class=\"wr_inner  course_type_container\" data-cource_type=\"posts_course\">");
    $page = substr($page, $before_pos);

    // Обрезаем текст до конца контента
    $after_pos = strpos($page, "<div class=\"wr_inner hidden course_type_container\" data-cource_type=\"cards_course\">");
    $page = substr($page, 0, $after_pos);
    
    // Собираем информацию про доллар
    $USD = $page;
    $USD_COURSE = array(
        "buy" => $page,
        "sell" => $page,
    );

    // Покупка доллара
    $USD_buy_before = strpos($USD_COURSE["buy"], "<td id=\"USD_buy\">");
    $USD_COURSE["buy"] = substr($USD_COURSE["buy"], $USD_buy_before);
    $USD_buy_after = strpos($USD_COURSE["buy"], "<td id=\"USD_sell\">");
    $USD_COURSE["buy"] = substr($USD_COURSE["buy"], 0, $USD_buy_after);

    // Продажа доллара
    $USD_sell_before = strpos($USD_COURSE["sell"], "<td id=\"USD_sell\">");
    $USD_COURSE["sell"] = substr($USD_COURSE["sell"], $USD_sell_before);
    $USD_sell_after = strpos($USD_COURSE["sell"], "<td>RUB</td>");
    $USD_COURSE["sell"] = substr($USD_COURSE["sell"], 0, $USD_sell_after);

    echo "Покупка     =>    Продажа<br>";
    echo "USD ".$USD_COURSE["buy"]. "=>" .$USD_COURSE["sell"];
}
