/**
 * Reviews slider.
 *
 * Progressive-enhancement carousel for the curated Google reviews. The track
 * uses native CSS scroll-snap, so without JS it's still a usable horizontal
 * scroller. This script adds prev/next buttons, paging dots, and keeps their
 * state in sync with the scroll position.
 *
 * @package Wellspring
 */
( function () {
	'use strict';

	var reduceMotion =
		window.matchMedia &&
		window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	function initSlider( slider ) {
		var track = slider.querySelector( '.ws-slider__track' );
		var slides = Array.prototype.slice.call(
			slider.querySelectorAll( '.ws-slider__slide' )
		);
		var prevBtn = slider.querySelector( '.ws-slider__nav--prev' );
		var nextBtn = slider.querySelector( '.ws-slider__nav--next' );
		var dotsWrap = slider.querySelector( '.ws-slider__dots' );

		if ( ! track || slides.length === 0 ) {
			return;
		}

		var pageStarts = [];

		function behavior() {
			return reduceMotion ? 'auto' : 'smooth';
		}

		// Distance between two adjacent slide starts (slide width + gap).
		function step() {
			if ( slides.length < 2 ) {
				return track.clientWidth;
			}
			return slides[ 1 ].offsetLeft - slides[ 0 ].offsetLeft;
		}

		function perView() {
			return Math.max( 1, Math.round( track.clientWidth / step() ) );
		}

		function hasOverflow() {
			return track.scrollWidth - track.clientWidth > 2;
		}

		// scrollLeft value that brings a given slide to the left edge.
		function leftFor( el ) {
			return (
				track.scrollLeft +
				( el.getBoundingClientRect().left -
					track.getBoundingClientRect().left )
			);
		}

		function buildPages() {
			pageStarts = [];
			var pv = perView();
			for ( var i = 0; i < slides.length; i += pv ) {
				pageStarts.push( slides[ i ] );
			}
		}

		function updateState() {
			var sl = track.scrollLeft;
			var maxScroll = track.scrollWidth - track.clientWidth;

			// Nearest page to current scroll position.
			var activeIndex = 0;
			var best = Infinity;
			pageStarts.forEach( function ( slide, i ) {
				var d = Math.abs( leftFor( slide ) - sl );
				if ( d < best ) {
					best = d;
					activeIndex = i;
				}
			} );
			// Snap last page active once we're at the very end.
			if ( sl >= maxScroll - 2 ) {
				activeIndex = pageStarts.length - 1;
			}

			Array.prototype.forEach.call(
				dotsWrap.children,
				function ( dot, i ) {
					var on = i === activeIndex;
					dot.classList.toggle( 'is-active', on );
					dot.setAttribute( 'aria-selected', on ? 'true' : 'false' );
					dot.tabIndex = on ? 0 : -1;
				}
			);

			if ( prevBtn ) {
				prevBtn.disabled = sl <= 2;
			}
			if ( nextBtn ) {
				nextBtn.disabled = sl >= maxScroll - 2;
			}
		}

		function build() {
			buildPages();
			dotsWrap.innerHTML = '';

			var overflow = hasOverflow() && pageStarts.length > 1;

			if ( prevBtn ) {
				prevBtn.hidden = ! overflow;
			}
			if ( nextBtn ) {
				nextBtn.hidden = ! overflow;
			}

			if ( ! overflow ) {
				return;
			}

			pageStarts.forEach( function ( slide, i ) {
				var dot = document.createElement( 'button' );
				dot.type = 'button';
				dot.className = 'ws-slider__dot';
				dot.setAttribute( 'role', 'tab' );
				dot.setAttribute(
					'aria-label',
					'Go to review group ' + ( i + 1 )
				);
				dot.addEventListener( 'click', function () {
					track.scrollTo( {
						left: leftFor( slide ),
						behavior: behavior(),
					} );
				} );
				dotsWrap.appendChild( dot );
			} );

			updateState();
		}

		function pageWidth() {
			return perView() * step();
		}

		if ( prevBtn ) {
			prevBtn.addEventListener( 'click', function () {
				track.scrollBy( {
					left: -pageWidth(),
					behavior: behavior(),
				} );
			} );
		}
		if ( nextBtn ) {
			nextBtn.addEventListener( 'click', function () {
				track.scrollBy( {
					left: pageWidth(),
					behavior: behavior(),
				} );
			} );
		}

		// Keep dot + button state synced while scrolling.
		var ticking = false;
		track.addEventListener( 'scroll', function () {
			if ( ticking ) {
				return;
			}
			ticking = true;
			window.requestAnimationFrame( function () {
				updateState();
				ticking = false;
			} );
		} );

		// Rebuild on resize (slides-per-view can change).
		var resizeTimer;
		window.addEventListener( 'resize', function () {
			window.clearTimeout( resizeTimer );
			resizeTimer = window.setTimeout( build, 150 );
		} );

		build();
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		var sliders = document.querySelectorAll( '[data-reviews-slider]' );
		Array.prototype.forEach.call( sliders, initSlider );
	} );
} )();
