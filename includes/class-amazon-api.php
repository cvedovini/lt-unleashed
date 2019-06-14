<?php

require_once('ApaiIO/Configuration/ConfigurationInterface.php');
require_once('ApaiIO/Configuration/GenericConfiguration.php');
require_once('ApaiIO/Configuration/Country.php');
require_once('ApaiIO/Operations/OperationInterface.php');
require_once('ApaiIO/Operations/AbstractOperation.php');
require_once('ApaiIO/Operations/Lookup.php');
require_once('ApaiIO/Operations/SimilarityLookup.php');
require_once('ApaiIO/Request/RequestFactory.php');
require_once('ApaiIO/Request/RequestInterface.php');
require_once('ApaiIO/Request/Util.php');
require_once('ApaiIO/Request/Rest/Request.php');
require_once('ApaiIO/ResponseTransformer/ResponseTransformerInterface.php');
require_once('ApaiIO/ResponseTransformer/ResponseTransformerFactory.php');
require_once('ApaiIO/ResponseTransformer/XmlToSimpleXmlObject.php');
require_once('ApaiIO/ApaiIO.php');

use ApaiIO\ApaiIO;
use ApaiIO\Configuration\GenericConfiguration;
use ApaiIO\Operations\Lookup;
use ApaiIO\Operations\SimilarityLookup;

class Amazon_API {

	function __construct() {
		$this->plugin = LibraryThing_Unleashed_Plugin::get_instance();

		$conf = new GenericConfiguration();
		$conf->setCountry($this->get_amzn_location());
		$conf->setAccessKey($this->plugin->get_option('aws_access_key'));
		$conf->setSecretKey($this->plugin->get_option('aws_secret_key'));
		$conf->setAssociateTag($this->get_amzn_associate_tag());
		$conf->setResponseTransformer('\ApaiIO\ResponseTransformer\XmlToSimpleXmlObject');
		$this->conf = $conf;
	}

	function lookup($isbn) {
		$cache_key = 'ltu_' . sha1('book'.$isbn);

		if (!isset($_REQUEST['ltu_nocache'])) {
			$book = get_transient($cache_key);
		}

		if (empty($book)) {
			$apaiIO = new ApaiIO($this->conf);
			$lookup = new Lookup();
			$lookup->setIdType(Lookup::TYPE_ISBN);
			$lookup->setItemId($isbn);
			$lookup->setResponseGroup(array('Small', 'Images', 'EditorialReview'));

			$response = $this->toObject($apaiIO->runOperation($lookup));				
			if (empty($response->Items->Item)) {
				return false;
			}

			if (is_array($response->Items->Item)) {
				$book = $response->Items->Item[0];
			} elseif (is_object($response->Items->Item)) {
				$book = $response->Items->Item;
			} else {
				return false;
			}
			
			set_transient($cache_key, $book);
		}

		return $book;
	}

	function similarityLookup($isbn) {
		$cache_key = 'ltu_' . sha1('similarity'.$isbn);

		if (!isset($_REQUEST['ltu_nocache'])) {
			$book = get_transient($cache_key);
		}

		if (empty($book)) {
			$apaiIO = new ApaiIO($this->conf);
			$lookup = new SimilarityLookup();
			$lookup->setItemId($isbn);
			$lookup->setResponseGroup(array('Small', 'Images'));

			$response = $this->toObject($apaiIO->runOperation($lookup));

			if (empty($response->Items->Item)) {
				return false;
			}

			if (is_array($response->Items->Item)) {
				$book = $response->Items->Item;
			} else {
				return false;
			}
			
			set_transient($cache_key, $book);
		}

		return $book;
	}

	function toObject($xml) {
		return json_decode(json_encode($xml));
	}

	function get_amzn_location() {
		return $this->plugin->get_option('amzn_locale', 'com');
	}

	function get_amzn_associate_tag() {
		$affiliate_tag = $this->plugin->get_option('affiliate_tag');

		if (empty($affiliate_tag)) {
			$locale = $this->plugin->get_option('amzn_locale', 'com');
			$tags = array(
					'ca' => 'librarforface-20',
					'de' => 'librarforfa0a-21',
					'fr' => 'fblibrarything-21',
					'co.uk' => 'librarforface-21',
					'com' => 'libraforfaceb-20',
			);

			return (isset($tags[$locale])) ? $tags[$locale] : '';
		}

		return $affiliate;
	}
}