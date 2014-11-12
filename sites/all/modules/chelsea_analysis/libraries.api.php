<?php

/**
 * @file
 * Documents API functions for Libraries module.
 */

/**
 * Return information about external libraries.
 *
 * @return
 *   An associative array whose keys are internal names of libraries and whose
 *   values are describing each library. Each key is the directory name below
 *   the 'libraries' directory, in which the library may be found. Each value is
 *   an associative array containing:
 *   - name: The official, human-readable name of the library.
 *   - vendor url: The URL of the homepage of the library.
 *   - download url: The URL of a web page on which the library can be obtained.
 *   - path: (optional) A relative path from the directory of the library to the
 *     actual library. Only required if the extracted download package contains
 *     the actual library files in a sub-directory.
 *   - library path: (optional) The absolute path to the library directory. This
 *     should not be declared normally, as it is automatically detected, to
 *     allow for multiple possible library locations. A valid use-case is an
 *     external library, in which case the full URL to the library should be
 *     specified here.
 *   - version: (optional) The version of the library. This should not be
 *     declared normally, as it is automatically detected (see 'version
 *     callback' below) to allow for version changes of libraries without code
 *     changes of implementing modules and to support different versions of a
 *     library simultaneously (though only one version can be installed per
 *     site). A valid use-case is an external library whose version cannot be
 *     determined programmatically. Either 'version' or 'version callback' (or
 *     'version arguments' in case libraries_get_version() is being used as a
 *     version callback) must be declared.
 *   - version callback: (optional) The name of a function that detects and
 *     returns the full version string of the library. The first argument is
 *     always $library, an array containing all library information as described
 *     here. There are two ways to declare the version callback's additional
 *     arguments, either as a single $options parameter or as multiple
 *     parameters, which correspond to the two ways to specify the argument
 *     values (see 'version arguments'). Defaults to libraries_get_version().
 *     Unless 'version' is declared or libraries_get_version() is being used as
 *     a version callback, 'version callback' must be declared. In the latter
 *     case, however, 'version arguments' must be declared in the specified way.
 *   - version arguments: (optional) A list of arguments to pass to the version
 *     callback. Version arguments can be declared either as an associative
 *     array whose keys are the argument names or as an indexed array without
 *     specifying keys. If declared as an associative array, the arguments get
 *     passed to the version callback as a single $options parameter whose keys
 *     are the argument names (i.e. $options is identical to the specified
 *     array). If declared as an indexed array, the array values get passed to
 *     the version callback as separate arguments in the order they were
 *     declared. The default version callback libraries_get_version() expects a
 *     single, associative array with named keys:
 *     - file: The filename to parse for the version, relative to the path
 *       speficied as the 'library path' property (see above). For example:
 *       'docs/changelog.txt'.
 *     - pattern: A string containing a regular expression (PCRE) to match the
 *       library version. For example: '@version\s+([0-9a-zA-Z\.-]+)@'. Note
 *       that the returned version is not the match of the entire pattern (i.e.
 *       '@version 1.2.3' in the above example) but the match of the first
 *       sub-pattern (i.e. '1.2.3' in the above example).
 *     - lines: (optional) The maximum number of lines to search the pattern in.
 *       Defaults to 20.
 *     - cols: (optional) The maximum number of characters per line to take into
 *       account. Defaults to 200. In case of minified or compressed files, this
 *       prevents reading the entire file into memory.
 *     Defaults to an empty array. 'version arguments' must be specified unless
 *     'version' is declared or the specified 'version callback' does not
 *     require any arguments. The latter might be the case with a
 *     library-specific version callback, for example.
 *   - files: An associative array of library files to load. Supported keys are:
 *     - js: A list of JavaScript files to load, using the same syntax as Drupal
 *       core's hook_library().
 *     - css: A list of CSS files to load, using the same syntax as Drupal
 *       core's hook_library().
 *     - php: A list of PHP files to load.
 *   - dependencies: An array of libraries this library depends on. Similar to
 *     declaring module dependencies, the dependency declaration may contain
 *     information on the supported version. Examples of supported declarations:
 *     @code
 *     $libraries['dependencies'] = array(
 *       // Load the 'example' library, regardless of the version available:
 *       'example',
 *       // Only load the 'example' library, if version 1.2 is available:
 *       'example (1.2)',
 *       // Only load a version later than 1.3-beta2 of the 'example' library:
 *       'example (>1.3-beta2)'
 *       // Only load a version equal to or later than 1.3-beta3:
 *       'example (>=1.3-beta3)',
 *       // Only load a version earlier than 1.5:
 *       'example (<1.5)',
 *       // Only load a version equal to or earlier than 1.4:
 *       'example (<=1.4)',
 *       // Combinations of the above are allowed as well:
 *       'example (>=1.3-beta2, <1.5)',
 *     );
 *     @endcode
 *   - variants: (optional) An associative array of available library variants.
 *     For example, the top-level 'files' property may refer to a default
 *     variant that is compressed. If the library also ships with a minified and
 *     uncompressed/source variant, those can be defined here. Each key should
 *     describe the variant type, e.g. 'minified' or 'source'. Each value is an
 *     associative array of top-level properties that are entirely overridden by
 *     the variant, most often just 'files'. Additionally, each variant can
 *     contain following properties:
 *     - variant callback: (optional) The name of a function that detects the
 *       variant and returns TRUE or FALSE, depending on whether the variant is
 *       available or not. The first argument is always $library, an array
 *       containing all library information as described here. The second
 *       argument is always a string containing the variant name. There are two
 *       ways to declare the variant callback's additional arguments, either as a
 *       single $options parameter or as multiple parameters, which correspond
 *       to the two ways to specify the argument values (see 'variant
 *       arguments'). If omitted, the variant is expected to always be
 *       available.
 *     - variant arguments: A list of arguments to pass to the variant callback.
 *       Variant arguments can be declared either as an associative array whose
 *       keys are the argument names or as an indexed array without specifying
 *       keys. If declared as an associative array, the arguments get passed to
 *       the variant callback as a single $options parameter whose keys are the
 *       argument names (i.e. $options is identical to the specified array). If
 *       declared as an indexed array, the array values get passed to the
 *       variant callback as separate arguments in the order they were declared.
 *     Variants can be version-specific (see 'versions').
 *   - versions: (optional) An associative array of supported library versions.
 *     Naturally, libraries evolve over time and so do their APIs. In case a
 *     library changes between versions, different 'files' may need to be
 *     loaded, different 'variants' may become available, or Drupal modules need
 *     to load different integration files adapted to the new version. Each key
 *     is a version *string* (PHP does not support floats as keys). Each value
 *     is an associative array of top-level properties that are entirely
 *     overridden by the version.
 *   - integration files: (optional) An associative array whose keys are module
 *     names and whose values are sets of files to load for the module, using
 *     the same notion as the top-level 'files' property. Each specified file
 *     should contain the path to the file relative to the module it belongs to.
 *   - callbacks: An associative array whose keys are callback groups and whose
 *     values are arrays of callbacks to apply to the library in that group.
 *     Each callback receives the following arguments:
 *     - $library: An array of library information belonging to the top-level
 *       library, a specific version, a specific variant or a specific variant
 *       of a specific version. Because library information such as the 'files'
 *       property (see above) can be declared in all these different locations
 *       of the library array, but a callback may have to act on all these
 *       different parts of the library, it is called recursively for each
 *       library with a certain part of the libraries array passed as $library
 *       each time.
 *     - $version: If the $library array belongs to a certain version (see
 *       above), a string containing the version. This argument may be empty, so
 *       NULL should be specified as the default value.
 *     - $variant: If the $library array belongs to a certain variant (see
 *       above), a string containing the variant name. This argument may be
 *       empty, so NULL should be specified as the default value.
 *     Valid callback groups are:
 *     - info: Callbacks registered in this group are applied after the library
 *       information has been retrieved via hook_libraries_info() or info files.
 *       At this point the following additional information is available:
 *       - $library['info type']: How the library information was obtained. Can
 *         be 'info file', 'module', or 'theme', depending on whether the
 *         library information was obtained from an info file, an enabled module
 *         or an enabled theme, respectively.
 *       Additionally, one of the following three keys is available, depending
 *       on the value of $library['info type'].
 *       - $library['info file']: In case the library information was obtained
 *         from an info file, the URI of the info file.
 *       - $library['module']: In case the library was obtained from an enabled
 *         module, the name of the providing module.
 *       - $library['theme']: In case the library was obtained from an enabled
 *         theme, the name of the providing theme.
 *     - pre-detect: Callbacks registered in this group are applied after the
 *       library path has been determined and before the version callback is
 *       invoked. At this point the following additional information is
 *       available:
 *       - $library['library path']: The path on the file system to the library.
 *     - post-detect: Callbacks registered in this group are applied after the
 *       library has been successfully detected. At this point the library
 *       contains the version-specific information, if specified, and following
 *       additional information is available:
 *       - $library['installed']: A boolean indicating whether the library is
 *         installed or not.
 *       - $library['version']: If it could be detected, a string containing the
 *         version of the library.
 *       - $library['variants'][$variant]['installed']: For each specified
 *         variant, a boolean indicating whether the variant is installed or
 *         not.
 *       Note that in this group the 'versions' property is no longer available.
 *     - pre-dependencies-load: Callbacks registered in this group are applied
 *       directly before the library's dependencies are loaded. At this point
 *       the library contains variant-specific information, if specified. Note
 *       that in this group the 'variants' property is no longer available.
 *     - pre-load: Callbacks registered in this group are applied directly after
 *       the library's dependencies are loaded and before the library itself is
 *       loaded.
 *     - post-load: Callbacks registered in this group are applied directly
 *       after this library is loaded. At this point, the library contains a
 *       'loaded' key, which contains the number of files that were loaded.
 *   Additional top-level properties can be registered as needed.
 *
 * @see hook_library()
 */
/*
function chelsea_analysis_libraries_info() {
  $libraries['PHPExcel'] = array(
    'name' => 'PHPExcel library',
    'vendor url' => 'http://phpexcel.codeplex.com/',
    'download url' => 'http://phpexcel.codeplex.com/',
    'files' => array(
      'PHP' => '/home/servilla/public_html/encuestas/sites/all/libraries/PHPExcel/Classes/PHPExcel.php'
    ),
  );
  return $libraries;
}*/