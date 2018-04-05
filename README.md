# avia-calculation
PHP 7.2 и MySQL 

Задача

Оптимальная центровка ВС:

Самолет состоит из секций.

Каждая секция характеризуется: индексом пассажира, максимальным количеством пассажиров, классом бронирования пассажира.

Суммарный индекс самолета равен сумме по всем секциям индекса секции умноженного на массу пассажиров в этой секции: 
таблица avia с данными для расчётов в файле db.sql 

Дано: количество пассажиров класса по классам B и Е, например, B = 10, E = 100

Масса пассажира - 80 кг

Требуется рассадить пассажиров по классам таким образом, чтобы суммарный индекс самолета по модулю был минимальным.

Требуемый результат: таблица с индексом секций самолета, код для рассчета на php, сохранение результата расчета в БД


