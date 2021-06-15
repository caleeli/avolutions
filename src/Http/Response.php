<?php
/**
 * AVOLUTIONS
 *
 * Just another open source PHP framework.
 *
 * @copyright   Copyright (c) 2019 - 2021 AVOLUTIONS
 * @license     MIT License (http://avolutions.org/license)
 * @link        http://avolutions.org
 */

namespace Avolutions\Http;

use Avolutions\View\View;

/**
 * Response class
 *
 * An object that contains the response of the request.
 *
 * @author	Alexander Vogt <alexander.vogt@avolutions.org>
 * @since	0.1.0
 */
class Response
{
	/**
	 * @var string $body The content of the response.
	 */
	public string $body;

    /**
     * setBody
     *
     * Fills the body of the Response with the passed value.
     *
     * @param string $value The value for the body
     */
    public function setBody(string $value)
    {
		$this->body = $value;
	}

	/**
	 * send
	 *
	 * Displays the content of the Response.
	 */
    public function send()
    {
        print $this->body;
	}
}