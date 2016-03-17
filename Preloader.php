<?php

/**
 * Extension allows preloading of custom content into all edit forms
 * when creating an article. <includeonly> and <noinclude> are respected
 * and stripped during new article population.
 *
 * @file
 * @ingroup Extensions
 * @author Troy Engel <troyengel@gmail.com>
 * @author Rob Church <robchur@gmail.com>
 */
 
if( !defined( 'MEDIAWIKI' ) ) {
	echo( "This file is an extension to the MediaWiki software and cannot be used standalone.\n" );
	exit( 1 );
}

$wgExtensionCredits['other'][] = array(
	'path'           => __FILE__,
	'name'           => 'Preloader',
	'author'         => 'Rob Church, Troy Engel',
	'version'        => '1.2.2',
	'url'            => 'https://github.com/troyengel/Preloader',
	'descriptionmsg' => 'preloader-desc',
);
$wgExtensionMessagesFiles['Preloader'] =  dirname(__FILE__) . '/Preloader.i18n.php';

/**
 * Sources of preloaded content for each namespace
 */
$wgPreloaderSource[ NS_MAIN ] = 'Template:Preload';

$wgHooks['EditFormPreloadText'][] = 'Preloader::mainHook';

class Preloader {

	/** Hook function for the preloading */
	public static function mainHook( &$text, &$title ) {
		$src = self::preloadSource( $title->getNamespace() );
		if( $src ) {
			$stx = self::sourceText( $src );
			if( $stx )
				$text = $stx;
		}
		return true;
	}

	/**
	 * Determine what page should be used as the source of preloaded text
	 * for a given namespace and return the title (in text form)
	 *
	 * @param $namespace Namespace to check for
	 * @return mixed
	 */ 
	static function preloadSource( $namespace ) {
		global $wgPreloaderSource;
		if( isset( $wgPreloaderSource[ $namespace ] ) ) {
			return $wgPreloaderSource[ $namespace ];
		} else {
			return false;
		}
	}

	/**
	 * Grab the current text of a given page if it exists
	 *
	 * @param $page Text form of the page title
	 * @return mixed
	 */
	static function sourceText( $page ) {
		$title = Title::newFromText( $page );
		if( $title && $title->exists() ) {
			$revision = Revision::newFromTitle( $title );
			return self::transform( $revision->getText() );
		} else {
			return false;
		}
	}

	/**
	 * Remove sections from the text and trim whitespace
	 *
	 * @param $text
	 * @return string
	 */
	static function transform( $text ) {
		$text = trim( preg_replace( '/<\/?includeonly>/s', '', $text ) );
		return trim( preg_replace( '/<noinclude>.*<\/noinclude>/s', '', $text ) );
	}
}
