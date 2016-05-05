<?php

namespace AmoCRM\Models;

/**
 * Class Task
 *
 * Класс модель для работы с Задачами
 *
 * @package AmoCRM\Models
 * @version 0.1.0
 * @author dotzero <mail@dotzero.ru>
 * @link http://www.dotzero.ru/
 * @link https://github.com/dotzero/amocrm-php
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Task extends Base
{
    /**
     * @var array Список доступный полей для модели (исключая кастомные поля)
     */
    protected $fields = [
        'element_id',
        'element_type',
        'date_create',
        'last_modified',
        'request_id',
        'task_type',
        'text',
        'responsible_user_id',
        'complete_till',
    ];

    /**
     * Сеттер для даты создания задачи
     *
     * @param string $date Дата в произвольном формате
     * @return $this
     */
    public function setDateCreate($date)
    {
        $this->values['date_create'] = strtotime($date);

        return $this;
    }

    /**
     * Сеттер для даты последнего изменения задачи
     *
     * @param string $date Дата в произвольном формате
     * @return $this
     */
    public function setLastModified($date)
    {
        $this->values['last_modified'] = strtotime($date);

        return $this;
    }

    /**
     * Сеттер для дата до которой необходимо завершить задачу
     *
     * Если указано время 23:59, то в интерфейсах системы
     * вместо времени будет отображаться "Весь день"
     *
     * @param string $date Дата в произвольном формате
     * @return $this
     */
    public function setCompleteTill($date)
    {
        $this->values['complete_till'] = strtotime($date);

        return $this;
    }

    /**
     * Список задач
     *
     * Метод для получения списка задач с возможностью фильтрации и постраничной выборки.
     * Ограничение по возвращаемым на одной странице (offset) данным - 500 задач
     *
     * @link https://developers.amocrm.ru/rest_api/tasks_list.php
     * @param array $parameters Массив параметров к amoCRM API
     * @param null|string $modified Дополнительная фильтрация по (изменено с)
     * @return array Ответ amoCRM API
     */
    public function apiList($parameters, $modified = null)
    {
        $response = $this->getRequest('/private/api/v2/json/tasks/list', $parameters, $modified);

        return isset($response['tasks']) ? $response['tasks'] : [];
    }

    /**
     * Добавление задачи
     *
     * Метод позволяет добавлять задачи по одной или пакетно
     *
     * @link https://developers.amocrm.ru/rest_api/tasks_set.php
     * @param array $tasks Массив задач для пакетного добавления
     * @return int|array Уникальный идентификатор задачи или массив при пакетном добавлении
     */
    public function apiAdd($tasks = [])
    {
        if (empty($tasks)) {
            $tasks = [$this];
        }

        $parameters = [
            'tasks' => [
                'add' => [],
            ],
        ];

        foreach ($tasks AS $task) {
            $parameters['tasks']['add'][] = $task->getValues();
        }

        $response = $this->postRequest('/private/api/v2/json/tasks/set', $parameters);

        if (isset($response['tasks']['add'])) {
            $result = array_map(function ($item) {
                return $item['id'];
            }, $response['tasks']['add']);
        } else {
            return false;
        }

        return count($tasks) == 1 ? array_shift($result) : $result;
    }

    /**
     * Обновление задачи
     *
     * Метод позволяет обновлять данные по уже существующим задачам
     *
     * @link https://developers.amocrm.ru/rest_api/tasks_set.php
     * @param int $id Уникальный идентификатор задачи
     * @param string $text Текст задачи
     * @param string $modified Дата последнего изменения данной сущности
     * @return bool Флаг успешности выполнения запроса
     */
    public function apiUpdate($id, $text, $modified = 'now')
    {
        $parameters = [
            'tasks' => [
                'update' => [],
            ],
        ];

        $task = $this->getValues();
        $task['id'] = $id;
        $task['text'] = $text;
        $task['last_modified'] = strtotime($modified);

        $parameters['tasks']['update'][] = $task;

        $response = $this->postRequest('/private/api/v2/json/tasks/set', $parameters);

        print_r($response);

        return isset($response['tasks']) ? true : false;
    }
}
