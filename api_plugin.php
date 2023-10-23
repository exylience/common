<?php

namespace MyPlugin\Includes;

class MyPluginApi
{
    public $api_url;

    public function get_vacancies($post)
    {
        $vacancies = [];

        if (! is_object($post)) {
            return false;
        }

        foreach ($this->process_vacancies() as $vacancy) {
            $vacancies[] = $vacancy;
        }

        return $vacancies;
    }

    public function find_vacancy($post, int $vacancyId)
    {
        if (! is_object($post)) {
            return false;
        }

        foreach ($this->process_vacancies() as $vacancy) {
            if ($vacancy['id'] === $vacancyId) {
                return $vacancy;
            }
        }

        return false;
    }

    private function process_vacancies()
    {
        $page = 0;

        while (true) {
            $data = $this->fetch_vacancies($page);

            if (! empty($data['objects'])) {
                yield $data['objects'];

                if ($data['more']) {
                    $page++;
                } else {
                    break;
                }
            } else {
                break;
            }
        }
    }

    private function fetch_vacancies(int $page)
    {
        $params = [
            'status' => 'all',
            'id_user' => $this->self_get_option('superjob_user_id'),
            'with_new_response' => 0,
            'order_field' => 'date',
            'order_direction' => 'desc',
            'count' => 100,
            'page' => $page,
        ];

        $response = $this->api_send('hr/vacancies', $params);

        if (is_wp_error($response)) {
            return false;
        }

        return json_decode($response['body'], true);
    }

    public function api_send(string $uri, array $params = [])
    {
        $getParams = http_build_query($params);

        // Можно условно использовать \WP_Http для отправки запроса
        return \WP_Http::request("$this->api_url/$uri?$getParams");
    }

    public function self_get_option($option_name)
    {
        // Тут также условно использую функцию get_option()
        return get_option($option_name);
    }
}