<?php
/**
 * Part of the Joomla Framework Crypt Package
 *
 * @copyright  Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Crypt;
use Joomla\Crypt\Cipher\CipherSimple;

/**
 * Crypt is a Joomla Framework class for handling basic encryption/decryption of data.
 *
 * @since  1.0
 */
class Crypt
{
	/**
	 * @var    CipherInterface  The encryption cipher object.
	 * @since  1.0
	 */
	private $cipher;

	/**
	 * @var    Key  The encryption key[/pair)].
	 * @since  1.0
	 */
	private $key;

	/**
	 * Object Constructor takes an optional key to be used for encryption/decryption. If no key is given then the
	 * secret word from the configuration object is used.
	 *
	 * @param   CipherInterface  $cipher  The encryption cipher object.
	 * @param   Key              $key     The encryption key[/pair)].
	 *
	 * @since   1.0
	 */
	public function __construct(CipherInterface $cipher = null, Key $key = null)
	{
		// Set the encryption cipher.
		$this->cipher = isset($cipher) ? $cipher : new CipherSimple;

		// Set the encryption key[/pair)].
		$this->key = isset($key) ? $key : $this->generateKey();
	}

	/**
	 * Method to decrypt a data string.
	 *
	 * @param   string  $data  The encrypted string to decrypt.
	 *
	 * @return  string  The decrypted data string.
	 *
	 * @since   1.0
	 */
	public function decrypt($data)
	{
		return $this->cipher->decrypt($data, $this->key);
	}

	/**
	 * Method to encrypt a data string.
	 *
	 * @param   string  $data  The data string to encrypt.
	 *
	 * @return  string  The encrypted data string.
	 *
	 * @since   1.0
	 */
	public function encrypt($data)
	{
		return $this->cipher->encrypt($data, $this->key);
	}

	/**
	 * Method to generate a new encryption key[/pair] object.
	 *
	 * @param   array  $options  Key generation options.
	 *
	 * @return  Key
	 *
	 * @since   1.0
	 */
	public function generateKey(array $options = array())
	{
		return $this->cipher->generateKey($options);
	}

	/**
	 * Method to set the encryption key[/pair] object.
	 *
	 * @param   Key  $key  The key object to set.
	 *
	 * @return  Crypt  Instance of $this to allow chaining.
	 *
	 * @since   1.0
	 */
	public function setKey(Key $key)
	{
		$this->key = $key;

		return $this;
	}

	/**
	 * Generate random bytes.
	 *
	 * @param   integer  $length  Length of the random data to generate
	 *
	 * @return  string  Random binary data
	 *
	 * @since   1.0
	 */
	public static function genRandomBytes($length = 16)
	{
		$sslStr = '';

		/*
		 * If a secure randomness generator exists use it.
		 */
		if (function_exists('openssl_random_pseudo_bytes'))
		{
			$sslStr = openssl_random_pseudo_bytes($length, $strong);

			if ($strong)
			{
				return $sslStr;
			}
		}

		return self::genRandomBytesCustom($length, $sslStr);
	}

	/**
	 * Generate random bytes using custom algorithm.
	 *
	 * @param   integer  $length           Length of the random data to generate
	 * @param   string   $initalRandomStr  Any random string to increase entropy
	 *
	 * @return  string  Random binary data
	 *
	 * @since   1.0
	 */
	public static function genRandomBytesCustom($length = 16, $initalRandomStr = null)
	{
		/*
		 * Collect any entropy available in the system along with a number
		 * of time measurements of operating system randomness.
		 */
		$bitsPerRound = 2;
		$maxTimeMicro = 400;
		$shaHashLength = 20;
		$randomStr = '';
		$total = $length;

		// Check if we can use /dev/urandom.
		$urandom = false;
		$handle = null;

		if (@is_readable('/dev/urandom'))
		{
			$handle = @fopen('/dev/urandom', 'rb');

			if ($handle)
			{
				$urandom = true;
			}
		}

		while ($length > strlen($randomStr))
		{
			$bytes = ($total > $shaHashLength)? $shaHashLength : $total;
			$total -= $bytes;
			$initalRandomStr = $initalRandomStr ? $initalRandomStr : '';
			/*
			 * Collect any entropy available from the PHP system and filesystem.
			 * If we have ssl data that isn't strong, we use it once.
			 */
			$entropy = rand() . uniqid(mt_rand(), true) . $initalRandomStr;
			$entropy .= implode('', @fstat(fopen(__FILE__, 'r')));
			$entropy .= memory_get_usage();
			$sslStr = '';

			if ($urandom)
			{
				stream_set_read_buffer($handle, 0);
				$entropy .= @fread($handle, $bytes);
			}
			else
			{
				/*
				 * There is no external source of entropy so we repeat calls
				 * to mt_rand until we are assured there's real randomness in
				 * the result.
				 *
				 * Measure the time that the operations will take on average.
				 */
				$samples = 3;
				$duration = 0;

				for ($pass = 0; $pass < $samples; ++$pass)
				{
					$microStart = microtime(true) * 1000000;
					$hash = sha1(mt_rand(), true);

					for ($count = 0; $count < 50; ++$count)
					{
						$hash = sha1($hash, true);
					}

					$microEnd = microtime(true) * 1000000;
					$entropy .= $microStart . $microEnd;

					if ($microStart >= $microEnd)
					{
						$microEnd += 1000000;
					}

					$duration += $microEnd - $microStart;
				}

				$duration = $duration / $samples;

				/*
				 * Based on the average time, determine the total rounds so that
				 * the total running time is bounded to a reasonable number.
				 */
				$rounds = (int) (($maxTimeMicro / $duration) * 50);

				/*
				 * Take additional measurements. On average we can expect
				 * at least $bitsPerRound bits of entropy from each measurement.
				 */
				$iter = $bytes * (int) ceil(8 / $bitsPerRound);

				for ($pass = 0; $pass < $iter; ++$pass)
				{
					$microStart = microtime(true);
					$hash = sha1(mt_rand(), true);

					for ($count = 0; $count < $rounds; ++$count)
					{
						$hash = sha1($hash, true);
					}

					$entropy .= $microStart . microtime(true);
				}
			}

			$randomStr .= sha1($entropy, true);
		}

		if ($urandom)
		{
			@fclose($handle);
		}

		return substr($randomStr, 0, $length);
	}
}
