<?php

use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\SlotRecord;

class Preloader {
	/** Hook function for the preloading */
	public static function mainHook( &$text, &$title ) {
		$src = self::preloadSource( $title->getNamespace() );
		if ( $src ) {
			$stx = self::sourceText( $src );
			if ( $stx ) {
				$text = $stx;
			}
		}
		return true;
	}

	/**
	 * Determine what page should be used as the source of preloaded text
	 * for a given namespace and return the title (in text form)
	 *
	 * @param int $namespace Namespace to check for
	 * @return string|bool Name of the page to be preloaded or bool false
	 */
	static function preloadSource( $namespace ) {
		global $wgPreloaderSource;
		if ( isset( $wgPreloaderSource[$namespace] ) ) {
			return $wgPreloaderSource[$namespace];
		} else {
			return false;
		}
	}

	/**
	 * Grab the current text of a given page if it exists
	 *
	 * @param string $page Text form of the page title
	 * @return string|bool
	 */
	static function sourceText( $page ) {
		$title = Title::newFromText( $page );
		if ( $title && $title->exists() ) {
			$revisionRecord = MediaWikiServices::getInstance()
				->getRevisionLookup()
				->getRevisionByTitle( $title );

			$user = RequestContext::getMain()->getUser();

			$content = $revisionRecord->getContent( SlotRecord::MAIN );
			$parserOptions = ParserOptions::newFromUser( $user );
			$transformed = $content->preloadTransform( $title, $parserOptions );

			return ContentHandler::getContentText( $transformed );
		} else {
			return false;
		}
	}
}
