<?php
/**
 * Project: JRF
 * User: MadFaill
 * Date: 25.09.14
 * Time: 14:40
 * File: Container.php
 * Package: jrf\container
 *
 */
namespace jrf\container;

/**
 * Class        Container
 * @description None.
 *
 * @author      MadFaill
 * @copyright   MadFaill 25.09.14
 * @since       25.09.14
 * @version     0.01
 * @package     jrf\container
 */
class Container implements \ArrayAccess
{
	/**
	 * @var array
	 */
	private $options = [];

	/**
	 * @param array $options
	 */
	public function __construct(array $options=[])
	{
		$this->options = $options;
	}

	/**
	 * @param $option
	 * @param null $default
	 * @return mixed|null
	 */
	public function get($option, $default=null)
	{
		return $this->offsetExists($option) ? $this->offsetGet($option) : $default;
	}

	/**
	 * @param $option
	 * @param $value
	 * @param bool $override
	 * @throws \RuntimeException
	 */
	public function set($option, $value, $override=false)
	{
		if ($this->offsetExists($option) && !$override) {
			throw new \RuntimeException("Option[$option] already set. To override - set override flag->true");
		}

		$this->offsetSet($option, $value);
	}

	/**
	 * @param $option
	 * @return mixed|null
	 */
	public function __get($option)
	{
		return $this->get($option);
	}

	/**
	 * @param $option
	 * @param $value
	 */
	public function __set($option, $value)
	{
		$this->set($option, $value);
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Whether a offset exists
	 * @link http://php.net/manual/en/arrayaccess.offsetexists.php
	 * @param mixed $offset <p>
	 * An offset to check for.
	 * </p>
	 * @return boolean true on success or false on failure.
	 * </p>
	 * <p>
	 * The return value will be casted to boolean if non-boolean was returned.
	 */
	public function offsetExists($offset)
	{
		return array_key_exists($offset, $this->options);
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Offset to retrieve
	 * @link http://php.net/manual/en/arrayaccess.offsetget.php
	 * @param mixed $offset <p>
	 * The offset to retrieve.
	 * </p>
	 * @return mixed Can return all value types.
	 */
	public function offsetGet($offset)
	{
		return $this->options[$offset];
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Offset to set
	 * @link http://php.net/manual/en/arrayaccess.offsetset.php
	 * @param mixed $offset <p>
	 * The offset to assign the value to.
	 * </p>
	 * @param mixed $value <p>
	 * The value to set.
	 * </p>
	 * @return void
	 */
	public function offsetSet($offset, $value)
	{
		$this->options[$offset] = $value;
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Offset to unset
	 * @link http://php.net/manual/en/arrayaccess.offsetunset.php
	 * @param mixed $offset <p>
	 * The offset to unset.
	 * </p>
	 * @return void
	 */
	public function offsetUnset($offset)
	{
		throw new \RuntimeException('Unable to unset container option');
	}
}

// ---------------------------------------------------------------------------------------------------------------------
// > END Container < #
// --------------------------------------------------------------------------------------------------------------------- 