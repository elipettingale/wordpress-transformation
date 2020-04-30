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
                foreach($fields as $attribute => $value) {
                    $this->acf_fields[] = $attribute;
                }
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

        foreach($this->acf_fields as $attribute) {
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

        if ($value = get_field($attribute, $this->item->ID)) {
            return $this->castValue($attribute, $value);
        }

        return $this->castValue($attribute, $this->item->$attribute);
    }

    public function getPermalinkAttribute()
    {
        return get_permalink($this->item->ID);
    }
}
