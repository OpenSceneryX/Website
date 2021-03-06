/**
 * @author aussi / https://github.com/aussig
 * Heavily derived from OBJLoader @author mrdoob / http://mrdoob.com/
 */

THREE.XPlanePolLoader = ( function () {

	function XPlanePolLoader( manager ) {

		this.manager = ( manager !== undefined ) ? manager : THREE.DefaultLoadingManager;

		this.material = new THREE.MeshLambertMaterial();
		this.material.transparent = true;

	}

	XPlanePolLoader.prototype = {

		constructor: XPlanePolLoader,

		load: function ( url, onLoad, onProgress, onError ) {

			var scope = this;
			var loader = new THREE.FileLoader( scope.manager );
			loader.setPath( this.path );
			loader.load( url, function ( text ) {

				onLoad( scope.parse( text ) );

			}, onProgress, onError );

		},

		setPath: function ( value ) {

			this.path = value;

			return this;

		},

		loadTexture: function ( path ) {

			THREE.XPlaneUtils.loadTexture(this.material, path, true);

		},

		parse: function ( text ) {

			console.time( 'XPlanePolLoader' );

			if ( text.indexOf( '\r\n' ) !== - 1 ) {

				// This is faster than String.split with regex that splits on both
				text = text.replace( /\r\n/g, '\n' );

			}

			var lines = text.split( '\n' );
			var line = '';

			// Faster to just trim left side of the line. Use if available.
			var trimLeft = ( typeof ''.trimLeft === 'function' );

			var scaleH, scaleV = 1;
			var surface = "default";

			for ( var i = 0, l = lines.length; i < l; i++ ) {

				line = lines[ i ];
				line = trimLeft ? line.trimLeft() : line.trim();

				// Ignore various unimportant lines
				if ( line.length === 0 ) continue;
				if ( line.charAt( 0 ) === '#' || line === 'I' || line === 'A' || line === '850' || line === 'DRAPED_POLYGON' ) continue;

				var data = line.split( /\s+/ );

				switch ( data[ 0 ] ) {

					case 'TEXTURE':
					case 'TEXTURE_NOWRAP':
					case 'TEXTURE_TILE':
						this.loadTexture( this.path + data[ 1 ] );
						break;

					case 'SCALE':
						scaleH = parseFloat(data[ 1 ]);
						scaleV = parseFloat(data[ 2 ]);
						break;

					case 'SURFACE':
						surface = data[ 1 ];
						break;

					case 'BUMP_LEVEL':
					case 'DECAL':
					case 'DECAL_KEYED':
					case 'DECAL_LIB':
					case 'DECAL_PARAMS':
					case 'DECAL_PARAMS_PROJ':
					case 'DECAL_RGBA':
					case 'DITHER_ALPHA':
					case 'LAYER_GROUP':
					case 'LOAD_CENTER':
					case 'NO_ALPHA':
					case 'NO_BLEND':
					case 'NO_SHADOW':
					case 'SPECULAR':
					case 'TEX_WIDTH':
					case 'TEXTURE_CONTROL':
					case 'TEXTURE_DETAIL':
					case 'TEXTURE_LIT':
					case 'TEXTURE_LIT_NOWRAP':
					case 'TEXTURE_NORMAL':
					case 'TWO_SIDED':
						// Lines we are ignoring right now (some may be implemented later)
						break;

					default:

						// Handle null terminated files without exception
						if ( line === '\0' ) continue;

						throw new Error( 'THREE.XPlanePolLoader: Unexpected line: "' + line + '"' );

				}

			}

			var container = new THREE.Group();

			// Create underlying surface to provide contrast for our polygon
			var geometry = new THREE.BoxGeometry( 1.5, 0.01, 1.5 );

			switch (surface) {
				case 'water': var surfaceColour = 0x000080; break;
				case 'concrete': var surfaceColour = 0xf4f4f4; break;
				case 'asphalt':
				case 'shoulder':
				case 'blastpad':
					var surfaceColour = 0x808080;
					break;
				case 'grass': var surfaceColour = 0x008000; break;
				case 'dirt':
				case 'lakebed':
					var surfaceColour = 0x907f6e;
					break;
				case 'gravel': var surfaceColour = 0xb8a880; break;
				case 'snow': var surfaceColour = 0xeeebf0; break;
				default: var surfaceColour = 0x008000;
			}
			var material = new THREE.MeshBasicMaterial( { color: surfaceColour } );
			var plane = new THREE.Mesh( geometry, material );
			container.add( plane );

			// Create polygon, dimensions proportional to loaded texture
			var normalisedPolygonWidth = scaleH / Math.max( scaleH, scaleV ); // 0 to 1
			var normalisedPolygonHeight = scaleV / Math.max( scaleH, scaleV ); // 0 to 1

			var vertices = [];
			vertices.push( normalisedPolygonWidth / 2, 0.02, normalisedPolygonHeight / 2 );
			vertices.push( normalisedPolygonWidth / 2, 0.02, -normalisedPolygonHeight / 2 );
			vertices.push( -normalisedPolygonWidth / 2, 0.02, -normalisedPolygonHeight / 2 );

			vertices.push( -normalisedPolygonWidth / 2, 0.02, -normalisedPolygonHeight / 2 );
			vertices.push( -normalisedPolygonWidth / 2, 0.02, normalisedPolygonHeight / 2 );
			vertices.push( normalisedPolygonWidth / 2, 0.02, normalisedPolygonHeight / 2 );

			var uvs = [];
			uvs.push( 1, 1 );
			uvs.push( 1, 0 );
			uvs.push( 0, 0 );

			uvs.push( 0, 0 );
			uvs.push( 0, 1 );
			uvs.push( 1, 1 );

			geometry = new THREE.BufferGeometry( );

			geometry.setAttribute( 'position', new THREE.Float32BufferAttribute( vertices, 3 ) );
			geometry.setAttribute( 'uv', new THREE.Float32BufferAttribute( uvs, 2 ) );
			geometry.computeVertexNormals();

			var polygon = new THREE.Mesh( geometry, this.material );
			container.add( polygon );

			console.timeEnd( 'XPlanePolLoader' );

			return container;
		}

	};

	return XPlanePolLoader;

} )();
