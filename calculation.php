<?php
//подключение к базе данных
$host = 'localhost';
$dbname = 'test';
$user = 'root';
$password = '1234';
    $db = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
?>
    <html>
    <body>
    <?php
    $data = []; // формируем данные для расчётов
    $stmt = $db->query('SELECT * FROM avia');
    while ($row = $stmt->fetch())
    {
        $class = $row['class'];
        if(!array_key_exists($class, $data)){
            $data[$class] = ['count' => 0];
        }
        $coef = (float)$row['coef'];
        $section = $row['section'];
        $num = $row['num'];
        $data[$class]['count']++;
        $data[$class][$section] = $num;
        $data[$class][$section . "_coef"] = $coef;
        $data[$class]['sections'][] = $section;
    }

    $params = ['B' => 10, 'E' => 101];
    $end = avia($params, $data);
    // db insert
    $params_json = json_encode($params);
    $avia_index = $end['index'];
    $result = json_encode($end['result']);
    $surlus = $end['error_count'];
    $sql = "INSERT INTO result (params, avia_index, result, surplus) VALUES ('" . $params_json . "', '" . $avia_index ."', '" . $result . "', '" . $surlus . "')";
    $db->query($sql);

    function avia(array $params, array $data){
        // количество доступных секций для каждого класса
        $result = [];
        $index = 0; //суммарный индекс самолёта
        $passenger = 80; // вес пассажира, кг
        $error_count = 0; // количество лишних пассажиров
        $end = [];

        foreach ($params as $key =>  $value){
            if($value > 0){
                $result[$key] = [];

                if($data[$key]['count'] > 1){
                    // несколько секций в классе, нужно выбирать
                    // пассажиров размещаем в секции по очереди, стараясь балансировать около нуля
                    for ($i = 0; $i < $value; $i++){
                        $test = [];
                        foreach ($data[$key]['sections'] as $section){
                            if(!array_key_exists($section, $result[$key])){
                                $result[$key][$section] = 0;
                            }
                            // если количество пассажиров не превышено
                            if($result[$key][$section] < $data[$key][$section]){
                                $test[$section] = abs($index + $passenger * $data[$key][$section . "_coef"]);
                            }
                        }
                        $min = min($test); // выбирам минимальный индекс, который получится при сложении текущего индекса с новыми по каждой секции
                        $section = array_search($min, $test); // выбранная секция
                        if($section){
                            $index += $data[$key][$section . "_coef"] * $passenger;
                            $result[$key][$section]++;
                        }else{
                            $error_count++;
                        }
                    }
                }else{
                    // только 1 секция в классе, все пассажиры размещаются тут
                    $section = $data[$key]['sections'][0];
                    if($value <= $data[$key][$section] ){
                        $index += $data[$key][$section . "_coef"] * $passenger * $value;
                        $result[$key][$section] = $value;
                    }else{
                        $index += $data[$key][$section . "_coef"] * $passenger * $data[$key][$section];
                        $result[$key][$section] = $data[$key][$section];
                        $error_count += $value - $data[$key][$section];
                    }
                }
            }
        }
        // обход массива результатов для возможной коррекции индекса
        foreach ($result as $key =>  $value){
            if(count($value) > 1){
                $diff = []; // все парные комбинации секций(как изменится индекс если пересадить пассажира из 1 секции в другую)
                $comb = array_keys($value);
                $array[1] = array_keys($comb);
                $size = sizeof($array[1]);
                $arr_name = 1;
                for ($count = 2; $count <= 2; $count++) {

                    $this_size = sizeof($array[$arr_name]);
                    $arr_name++;
                    for ($i = 0; $i < $this_size; $i++) {
                        $gg = $array[($arr_name-1)][$i];

                        for ($x = 0; $x < $size; $x++) {
                            $temp = str_split ($gg . $array[1][$x]); // строка в массив
                            $length = count($temp); // длина массива
                            $min_temp = array_unique($temp); // убираем повторящиеся значения
                            $min_length = count($min_temp); // новая длина для сравнения
                            if($length == $min_length){
                                sort($temp);
                                $array[$arr_name][] = implode('-', $temp);
                            }
                        }
                    }

                    if ($arr_name > 2){
                        unset($array[($arr_name-1)]);
                    }
                }
                $groups_array = array_unique($array[$arr_name]);
                $groups_array = array_values($groups_array);

                foreach ($groups_array as $v){
                    $arr = explode('-', $v);
                    $diff[$comb[$arr[0]] . '_' . $comb[$arr[1]]] =  - $data[$key][$comb[$arr[0]] . "_coef"] + $data[$key][$comb[$arr[1]] . "_coef"];
                    $diff[$comb[$arr[1]] . '_' . $comb[$arr[0]]] =  + $data[$key][$comb[$arr[0]] . "_coef"] - $data[$key][$comb[$arr[1]] . "_coef"];
                }

                if(!$error_count){
                    $end = combinations($result, $data, $index, $diff, $key, $passenger);
                    $result = $end['result'];
                    $index = $end['index'];
                }else{
                    $end = [
                        'result' => $result,
                        'index' => $index
                    ];
                }
            }else{
                $end = [
                    'result' => $result,
                    'index' => $index
                ];
            }
        }

        $end['error_count'] = $error_count;
        return $end;
    }

    // рекурсивная функция для того чтобы пересадить некоторых пассажиров в другие секции для максимального приближения индекса к нулю
    function combinations(array $result, array $data, $index, array $diff, $key, $passenger){
        $res = []; // массив новых индексов при пересадке одного пассажира из 1 секции в другую
        foreach ($diff as $two => $val){
            $two_sections = explode('_', $two);
            $s2 = $two_sections[1];
            // если есть свободные места в секции
            if($result[$key][$s2] < $data[$key][$s2]){
                $res[$two] = abs($index + $passenger * $val);
            }
        }
        $min_diff = min($res);
        // если один из новых индексов по модулю ближе к нулю - меняем результат
        if($min_diff && abs($min_diff) < abs($index)){
            $index = $min_diff;
            $s = array_search($min_diff, $res); // выбранная секция
            $change_sections = explode('_', $s);
            $result[$key][$change_sections[0]]--;
            $result[$key][$change_sections[1]]++;
            combinations($result, $data, $index, $diff, $key, $passenger);
        }
        return [
            'result' => $result,
            'index' => $index
        ];
    }
    ?>
    </body>
    </html>
