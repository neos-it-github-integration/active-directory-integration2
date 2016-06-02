<?php

/**
 * @author Tobias Hellmann <the@neos-it.de>
 * @access private
 */
class Ut_Core_Util_StringUtilTest extends Ut_BasicTest
{
	/**
	 * @test
	 */
	public function split_removesCarriageReturnAndSplitsStringByNewLines()
	{
		$string = "This \r\n is \r\n a \r\n test \r\n string.";
		$expected = array(
			'This ',
			' is ',
			' a ',
			' test ',
			' string.',
		);

		$actual = Core_Util_StringUtil::split($string, "\n");

		$this->assertEquals($expected, $actual);
	}

	/**
	 * @issue ADI-248
	 * @test
	 */
	public function ADI248_splitText_onlyReturnsNonEmptyLines() {
		$string  = "  ; ; test ";

		$expected = array("test");

		$actual = Core_Util_StringUtil::splitNonEmpty($string, ";");
		$this->assertEquals($expected, $actual);
	}

	/**
	 * @test
	 */
	public function splitText_withUnixLineBreaks_returnLinesInArray()
	{
		$string = 'abba' . "\n" . 'cddc' . "\n";

		$expected = array(
			'abba',
			'cddc'
		);

		$actual = Core_Util_StringUtil::splitText($string);
		$this->assertEquals($expected, $actual);
	}

	/**
	 * @test
	 */
	public function splitText_withWindowLineBreaks_returnLinesInArray()
	{
		$string = 'abba' . "\r\n" . 'cddc' . "\n\r" . 'effe';

		$expected = array(
			'abba',
			'cddc',
			'effe'
		);

		$actual = Core_Util_StringUtil::splitText($string);
		$this->assertEquals($expected, $actual);
	}

	/**
	 * @test
	 */
	public function splitText_withBeginningWhitespaces_returnTrimmedValues()
	{
		$string = '  a bba  ';

		$expected = array(
			'a bba'
		);

		$actual = Core_Util_StringUtil::splitText($string);
		$this->assertEquals($expected, $actual);
	}

	/**
	 * @test
	 */
	public function splitText_withEmptyLines_returnNotEmptyLines()
	{
		$string = "\n" . 'abba' . "\n" . '     ' . "\n" . '' . "\n" . 'cddc';

		$expected = array(
			'abba',
			'cddc'
		);

		$actual = Core_Util_StringUtil::splitText($string);
		$this->assertEquals($expected, $actual);
	}
}