
/**
 * Practitioner Person node, for author / reviewedBy references.
 *
 * Separate settings rather than reading the home page's practitioner fields:
 * structured data asserts facts to search engines, so the values are stated
 * deliberately in one place instead of inherited from marketing copy that
 * someone might reword.
 *
 * Every property is omitted when empty. A half-filled credential is worse than
 * no credential — wrong structured data is a liability, and sameAs in
 * particular must never contain a guessed profile URL.
 */
add_action(
	'acf/init',
	function () {
		if ( ! function_exists( 'acf_add_local_field_group' ) || wellspring_seo_plugin_active() ) {
			return;
		}

		acf_add_local_field_group(
			array(
				'key'        => 'group_wellspring_practitioner_schema',
				'title'      => 'Practitioner (structured data)',
				'fields'     => array(
					array(
						'key'     => 'field_ws_person_note',
						'label'   => '',
						'type'    => 'message',
						'message' => 'Used to describe Dr. Cowburn to search engines as the author and reviewer of the clinic cases. This is not a display setting &mdash; nothing here appears on the page. Leave a field empty and it is left out of the markup entirely; an empty field is always safer than a guess.',
					),
					array(
						'key'   => 'field_ws_person_name',
						'name'  => 'person_name',
						'label' => 'Full name',
						'type'  => 'text',
					),
					array(
						'key'          => 'field_ws_person_job',
						'name'         => 'person_job_title',
						'label'        => 'Professional title',
						'instructions' => 'e.g. Doctor of Traditional Chinese Medicine and Registered Acupuncturist.',
						'type'         => 'text',
					),
					array(
						'key'          => 'field_ws_person_degree',
						'name'         => 'person_degree',
						'label'        => 'Qualification',
						'instructions' => 'The credential awarded, e.g. Doctor of Traditional Chinese Medicine and Acupuncture.',
						'type'         => 'text',
					),
					array(
						'key'          => 'field_ws_person_school',
						'name'         => 'person_school',
						'label'        => 'Awarding institution',
						'instructions' => 'Where the qualification was earned. Used for both <code>alumniOf</code> and the credential&rsquo;s awarding body.',
						'type'         => 'text',
					),
					array(
						'key'   => 'field_ws_person_school_short',
						'name'  => 'person_school_short',
						'label' => 'Institution abbreviation',
						'type'  => 'text',
					),
					array(
						'key'          => 'field_ws_person_regulator',
						'name'         => 'person_regulator',
						'label'        => 'Regulator',
						'instructions' => 'The professional body she is registered with. Used for <code>memberOf</code> and the licence credential.',
						'type'         => 'text',
					),
					array(
						'key'   => 'field_ws_person_regulator_short',
						'name'  => 'person_regulator_short',
						'label' => 'Regulator abbreviation',
						'type'  => 'text',
					),
					array(
						'key'          => 'field_ws_person_licence',
						'name'         => 'person_licence',
						'label'        => 'Registration / licence name',
						'instructions' => 'e.g. Registered Acupuncturist (Alberta).',
						'type'         => 'text',
					),
					array(
						'key'          => 'field_ws_person_sameas',
						'name'         => 'person_sameas',
						'label'        => 'Profile URLs',
						'instructions' => 'One per line &mdash; LinkedIn, a regulator directory listing, a professional association profile. These become <code>sameAs</code>, which is how a search engine ties this page&rsquo;s author to the same person elsewhere. <strong>Only add URLs you have checked resolve to her.</strong>',
						'type'         => 'textarea',
						'rows'         => 4,
						'new_lines'    => '',
					),
				),
				'location'   => array(
					array(
						array(
							'param'    => 'options_page',
							'operator' => '==',
							'value'    => 'wellspring-settings',
						),
					),
				),
				'menu_order' => 15,
				'active'     => true,
			)
		);
	}
);

/**
 * The Person node for the practitioner, or null when there is nothing to say.
 *
 * A stable @id anchored to the About page, so other nodes on any page can point
 * at the same entity rather than describing a new one each time.
 *
 * @return array|null
 */
function wellspring_practitioner_person() {
	static $cache = false;
	if ( false !== $cache ) {
		return $cache;
	}

	if ( ! function_exists( 'get_field' ) ) {
		$cache = null;
		return $cache;
	}

	$get = function ( $name ) {
		return trim( (string) get_field( $name, 'option' ) );
	};

	$name = $get( 'person_name' );

	// Without a name there is no entity to describe.
	if ( '' === $name ) {
		$cache = null;
		return $cache;
	}

	$about = function_exists( 'wellspring_page_url' ) ? wellspring_page_url( 'about' ) : home_url( '/' );

	$person = array(
		'@type' => 'Person',
		'@id'   => $about . '#practitioner',
		'name'  => $name,
		'url'   => $about,
	);

	$job = $get( 'person_job_title' );
	if ( '' !== $job ) {
		$person['jobTitle'] = $job;
	}

	// Portrait, if the About/home practitioner image is set. Real value or none.
	$portrait = get_field( 'practitioner_portrait', (int) get_option( 'page_on_front' ) );
	if ( is_array( $portrait ) && ! empty( $portrait['url'] ) ) {
		$person['image'] = $portrait['url'];
	}

	$school       = $get( 'person_school' );
	$school_short = $get( 'person_school_short' );
	$regulator    = $get( 'person_regulator' );
	$reg_short    = $get( 'person_regulator_short' );
	$degree       = $get( 'person_degree' );
	$licence      = $get( 'person_licence' );

	// hasCredential — only the credentials we can actually name.
	$credentials = array();

	if ( '' !== $degree ) {
		$cred = array(
			'@type'              => 'EducationalOccupationalCredential',
			'credentialCategory' => 'degree',
			'name'               => $degree,
		);
		if ( '' !== $school ) {
			$cred['recognizedBy'] = array_filter(
				array(
					'@type'         => 'EducationalOrganization',
					'name'          => $school,
					'alternateName' => $school_short,
				)
			);
		}
		$credentials[] = $cred;
	}

	if ( '' !== $licence ) {
		$cred = array(
			'@type'              => 'EducationalOccupationalCredential',
			'credentialCategory' => 'license',
			'name'               => $licence,
		);
		if ( '' !== $regulator ) {
			$cred['recognizedBy'] = array_filter(
				array(
					'@type'         => 'Organization',
					'name'          => $regulator,
					'alternateName' => $reg_short,
				)
			);
		}
		$credentials[] = $cred;
	}

	if ( $credentials ) {
		$person['hasCredential'] = $credentials;
	}

	if ( '' !== $school ) {
		$person['alumniOf'] = array_filter(
			array(
				'@type'         => 'EducationalOrganization',
				'name'          => $school,
				'alternateName' => $school_short,
			)
		);
	}

	if ( '' !== $regulator ) {
		$person['memberOf'] = array_filter(
			array(
				'@type'         => 'Organization',
				'name'          => $regulator,
				'alternateName' => $reg_short,
			)
		);
	}

	$person['worksFor'] = array(
		'@type' => 'Organization',
		'@id'   => home_url( '/#organization' ),
		'name'  => get_bloginfo( 'name' ),
		'url'   => home_url( '/' ),
	);

	// sameAs — never guessed. Only URLs an editor has entered.
	$same = array();
	foreach ( preg_split( '/\r\n|\r|\n/', $get( 'person_sameas' ) ) as $line ) {
		$line = trim( $line );
		if ( '' === $line ) {
			continue;
		}
		$url = esc_url_raw( $line );
		if ( $url ) {
			$same[] = $url;
		}
	}
	if ( $same ) {
		$person['sameAs'] = array_values( array_unique( $same ) );
	}

	$cache = $person;
	return $cache;
}
