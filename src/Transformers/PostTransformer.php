<?php

namespace EliPett\Transformation\Transformers;

use EliPett\Transformation\Transformers\Transformer;
use WP_Post;

/**
 * Class PostTransformer
 * @package EliPett\Transformation\Transformers
 *
 * @property WP_Post $item
 * @property array $includes
 * @property array $excludes
 * @property array $rename
 */
class PostTransformer extends Transformer
{
    protected $acf_fields = [];

    public function __construct($item)
    {
        parent::__construct($item);

        if (function_exists('get_fields')) {
            $fields = get_fields($item->ID);

            if ($fields) {
                $this->acf_fields = $fields;
            }
        }
    }

    protected function attributes()
    {
        $attributes = [
            'ID',
            'post_title',
            'post_date',
            'post_status',
            'post_content'
        ];

        foreach($this->acf_fields as $attribute => $value) {
            $attributes[] = $attribute;
        }

        $methods = preg_grep('/get(.*)Attribute/', get_class_methods($this));

        foreach($methods as $method) {
            $attribute = substr($method, 3, strlen($method) - 12);
            $attributes[] = lower_snake_case($attribute);
        }

        return $attributes;
    }

    protected function getValue($attribute)
    {
        $method = 'get' . upper_camel_case($attribute) . 'Attribute';

        if (method_exists($this, $method)) {
            return $this->castValue($attribute, $this->$method());
        }

        if (array_key_exists($attribute, $this->acf_fields)) {
            return $this->castValue($attribute, $this->acf_fields[$attribute]);
        }

        return $this->castValue($attribute, $this->item->$attribute);
    }

    public function getPermalinkAttribute()
    {
        return get_permalink($this->item->ID);
    }
}
