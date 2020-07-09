<?php

namespace Core;

class View
{
    public static function render($view, $args = [], $extension = 'phtml')
    {
        extract($args, EXTR_SKIP);

        $file = dirname(__DIR__) . "/App/Views/" . $view . '.' . $extension;

        if (is_readable($file)) {
            require $file;
        } else {
            throw new \Exception("$file non trouvé.");
        }

        return true;
    }

    public static function set_values($name, $value = NULL)
    {
        if (!empty($_POST[$name])) {
            return $_POST[$name];
        }
        if (!empty($value)) {
            return $value;
        }
        return '';
    }

    /**
     * Set Select
     *
     * Enables pull-down lists to be set to the value the user
     * selected in the event of an error
     * @param string|null $field
     * @param string|null $value
     * @param bool|null $default
     * @return    string
     */
	public static function set_select(?string $field = '', ?string $value = '', ?bool $default = false)
	{
		if (!isset($_POST[$field])) {
			return ($default === TRUE && count($_POST) === 0) ? ' selected="selected"' : '';
		}

		$field = $_POST[$field];
		$value = (string) $value;
		if (is_array($field)) {
			// Note: in_array('', array(0)) returns TRUE, do not use it
			foreach ($field as &$v) {
				if ($value === $v) {
					return ' selected="selected"';
				}
			}

			return '';
		}
		elseif (($field === '' OR $value === '') OR ($field !== $value)) {
			return '';
		}

		return ' selected="selected"';
	}
}
