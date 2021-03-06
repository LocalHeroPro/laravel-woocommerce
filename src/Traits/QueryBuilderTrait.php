<?php

namespace Codexshaper\WooCommerce\Traits;

use Codexshaper\WooCommerce\Facades\WooCommerce;

trait QueryBuilderTrait
{
    /**
     * @var $options
     */
    protected $options = [];
    /**
     * @var $options
     */
    protected $where = [];
    /**
     * @var $results
     */
    protected $properties = [];

    /**
     * Retrieve all Items
     *
     * @param array $options
     *
     * @return array
     */
    protected function all($options = [])
    {
        return WooCommerce::all($this->endpoint, $options);
    }

    /**
     * Retrieve single Item
     *
     * @param integer $id
     * @param array $options
     *
     * @return object
     */
    protected function find($id, $options = [])
    {
        return collect(WooCommerce::find("{$this->endpoint}/{$id}", $options));
    }

    /**
     * Create new Item
     *
     * @param array $data
     *
     * @return object
     */
    protected function create($data)
    {
        return WooCommerce::create($this->endpoint, $data);
    }

    /**
     * Update Existing Item
     *
     * @param integer $id
     * @param array $data
     *
     * @return object
     */
    protected function update($id, $data)
    {
        return WooCommerce::update("{$this->endpoint}/{$id}", $data);
    }

    /**
     * Destroy Item
     *
     * @param integer $id
     * @param array $options
     *
     * @return object
     */
    protected function delete($id, $options = [])
    {
        return WooCommerce::delete("{$this->endpoint}/{$id}", $options);
    }

    protected function batch($data)
    {
        return WooCommerce::create('{$this->endpoint}/batch', $options);
    }

    /**
     * Retrieve data
     *
     * @return array
     */
    protected function get()
    {
        $results = WooCommerce::all($this->endpoint, $this->options);

        if (empty($this->where)) {
            return $results;
        }
        $filteredResults = [];
        foreach ($this->where as $key => $where) {

            foreach ($results as $result) {
                $name      = $where['name'];
                $name      = $result->$name;
                $operator  = ($where['operator'] == '=') ? $where['operator'] . "=" : $where['operator'];
                $value     = $where['value'];
                $condition = "'$name' $operator '$value'";
                if (eval("return $condition;")) {
                    $filteredResults[] = $result;
                }
            }
        }

        return $filteredResults;
    }

    /**
     * Retrieve data
     *
     * @return object
     */
    protected function first()
    {
        return collect($this->get()[0]);
    }

    /**
     * Set options for woocommerce request
     *
     * @param array $parameters
     *
     * @return object $this
     */
    protected function options($parameters)
    {
        if (!is_array($parameters)) {
            throw new \Exception("Options must be an array", 1);
        }

        if (empty($parameters)) {
            throw new \Exception("Options must be pass at least one element", 1);
        }

        foreach ($parameters as $key => $value) {

            $this->options[$key] = $value;
        }

        return $this;
    }

    /**
     * Join options for woocommerce request
     *
     * @param array $parameters
     *
     * @return object $this
     */
    protected function where(...$parameters)
    {
        if (count($parameters) == 3) {
            $where = [
                'name'     => $parameters[0],
                'operator' => $parameters[1],
                'value'    => $parameters[2],
            ];
            $this->where[] = $where;
        }

        if (count($parameters) == 2) {
            $this->options[$parameters[0]] = $parameters[1];
        }

        if (count($parameters) == 1) {
            foreach ($parameters as $parameter) {
                foreach ($parameter as $key => $value) {
                    $this->options[$key] = $value;
                }
            }
        }
        return $this;
    }

    /**
     *
     * @param string $name
     * @param string $direction
     *
     * @return object $this
     */
    protected function orderBy($name, $direction = 'desc')
    {
        $this->options['orderby'] = $name;
        $this->options['order']   = $direction;

        return $this;
    }

    /**
     *
     * @param integer $per_page
     * @param integer $current_page
     *
     * @return array
     */
    protected function paginate($per_page, $current_page = 1)
    {
        $this->options['per_page'] = (int) $per_page;

        if ($current_page > 0) {
            $this->options['page'] = (int) $current_page;
        }

        $results      = $this->get();
        $totalResults = WooCommerce::countResults();
        $totalPages   = WooCommerce::countPages();
        $currentPage  = WooCommerce::current();
        $previousPage = WooCommerce::previous();
        $nextPage     = WooCommerce::next();

        $pagination = [
            'total_results' => $totalResults,
            'total_pages'   => $totalPages,
            'current_page'  => $currentPage,
            'previous_page' => $previousPage,
            'next_page'     => $nextPage,
            'first_page'    => 1,
            'last_page'     => $totalResults,
        ];

        $results['pagination'] = $pagination;

        return $results;
    }

    /**
     *
     * @return array
     */
    public function save()
    {
        $this->results = WooCommerce::create($this->endpoint, $this->properties);

        return $this->results;
    }
}
