<?php
/**
 * @copyright  Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Crypt\Tests;

use Joomla\Crypt\Key;
use Joomla\Crypt\Cipher\CipherSimple;

/**
 * Test class for \Joomla\Crypt\Cipher\CipherSimple.
 *
 * @since  1.0
 */
class CipherSimpleTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var    CipherSimple
	 * @since  1.0
	 */
	private $cipher;

	/**
	 * Prepares the environment before running a test.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->cipher = new CipherSimple;

		$this->key = new Key('simple');
		$this->key->private = 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCUgkVF4mLxAUf80ZJPAJHXHoac';
		$this->key->public = 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCUgkVF4mLxAUf80ZJPAJHXHoac';
	}

	/**
	 * Cleans up the environment after running a test.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function tearDown()
	{
		$this->cipher = null;
		$this->key = null;

		parent::tearDown();
	}

	/**
	 * Test...
	 *
	 * @return  array
	 *
	 * @since   1.3.0
	 */
	public function dataForEncrypt()
	{
		return array(
			array(
				'1.txt',
				'c-;3-(Is>{DJzOHMCv_<#yKuN/G`/Us{GkgicWG$M|HW;kI0BVZ^|FY/"Obt53?PNaWwhmRtH;lWkWE4vlG5CIFA!abu&F=Xo#Qw}gAp3;GL\'k])%D}C+W&ne6_F$3P5'),
			array(
				'2.txt',
				'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. ' .
					'Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor ' .
					'in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt ' .
					'in culpa qui officia deserunt mollit anim id est laborum.'),
			array('3.txt', 'لا أحد يحب الألم بذاته، يسعى ورائه أو يبتغيه، ببساطة لأنه الألم...'),
			array('4.txt',
				'Широкая электрификация южных губерний даст мощный ' .
					'толчок подъёму сельского хозяйства'),
			array('5.txt', 'The quick brown fox jumps over the lazy dog.')
		);
	}

	/**
	 * Tests CipherSimple->decrypt()
	 *
	 * @param   string  $file  @todo
	 * @param   string  $data  @todo
	 *
	 * @return  void
	 *
	 * @covers        Joomla\Crypt\Cipher\CipherSimple::decrypt
	 * @covers        Joomla\Crypt\Cipher\CipherSimple::_hexToInt
	 * @covers        Joomla\Crypt\Cipher\CipherSimple::_hexToIntArray
	 * @dataProvider  dataForEncrypt
	 * @since         1.0
	 */
	public function testDecrypt($file, $data)
	{
		$encrypted = file_get_contents(__DIR__ . '/stubs/encrypted/simple/' . $file);
		$decrypted = $this->cipher->decrypt($encrypted, $this->key);

		// Assert that the decrypted values are the same as the expected ones.
		$this->assertEquals(
			$data,
			$decrypted
		);
	}

	/**
	 * Tests CipherSimple->decrypt()
	 *
	 * @return  void
	 *
	 * @covers             Joomla\Crypt\Cipher\CipherSimple::decrypt
	 * @expectedException  \InvalidArgumentException
	 * @since              1.3.0
	 */
	public function testDecryptInvalidKeyType()
	{
		// Build the key for testing.
		$key = new Key('3des');
		$this->key->private = file_get_contents(__DIR__ . '/stubs/encrypted/3des/key.priv');
		$this->key->public = file_get_contents(__DIR__ . '/stubs/encrypted/3des/key.pub');

		$file = '5.txt';
		$expected = 'The quick brown fox jumps over the lazy dog.';

		$encrypted = file_get_contents(__DIR__ . '/stubs/encrypted/simple/' . $file);
		$decrypted = $this->cipher->decrypt($encrypted, $key);

		// Assert that the decrypted values are the same as the expected ones.
		$this->assertEquals(
			$data,
			$decrypted
		);
	}

	/**
	 * Tests CipherSimple->encrypt()
	 *
	 * @param   string  $file  @todo
	 * @param   string  $data  @todo
	 *
	 * @return  void
	 *
	 * @covers        Joomla\Crypt\Cipher\CipherSimple::encrypt
	 * @covers        Joomla\Crypt\Cipher\CipherSimple::_intToHex
	 * @dataProvider  dataForEncrypt
	 * @since         1.0
	 */
	public function testEncrypt($file, $data)
	{
		$encrypted = $this->cipher->encrypt($data, $this->key);

		// Assert that the encrypted value is not the same as the clear text value.
		$this->assertNotEquals($data, $encrypted);

		// Assert that the encrypted values are the same as the expected ones.
		$this->assertStringEqualsFile(
			__DIR__ . '/stubs/encrypted/simple/' . $file,
			$encrypted
		);
	}

	/**
	 * Tests CipherSimple->encrypt()
	 *
	 * @return  void
	 *
	 * @covers             Joomla\Crypt\Cipher\CipherSimple::encrypt
	 * @expectedException  \InvalidArgumentException
	 * @since              1.3.0
	 */
	public function testEncryptInvalidKeyType()
	{
		// Build the key for testing.
		$key = new Key('3des');
		$this->key->private = file_get_contents(__DIR__ . '/stubs/encrypted/3des/key.priv');
		$this->key->public = file_get_contents(__DIR__ . '/stubs/encrypted/3des/key.pub');

		$file = '5.txt';
		$data = 'The quick brown fox jumps over the lazy dog.';

		$encrypted = $this->cipher->encrypt($data, $key);

		// Assert that the encrypted value is not the same as the clear text value.
		$this->assertNotEquals(
			$data,
			$encrypted
		);

		// Assert that the encrypted values are the same as the expected ones.
		$this->assertStringEqualsFile(
			__DIR__ . '/stubs/encrypted/simple/' . $file,
			$encrypted
		);
	}

	/**
	 * Tests CipherSimple->generateKey()
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Crypt\Cipher\CipherSimple::generateKey
	 * @covers  Joomla\Crypt\Cipher\CipherSimple::_getRandomKey
	 * @since   1.0
	 */
	public function testGenerateKey()
	{
		$key = $this->cipher->generateKey();

		// Assert that the key is the correct type.
		$this->assertInstanceOf(
			'Joomla\\Crypt\\Key',
			$key
		);

		// Assert the public and private keys are the same.
		$this->assertEquals(
			$key->public,
			$key->private
		);

		// Assert the key is of the correct type.
		$this->assertAttributeEquals(
			'simple',
			'type',
			$key
		);
	}
}
