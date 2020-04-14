/**
 * @author aussi / https://github.com/aussig
 * Heavily derived from OBJLoader @author mrdoob / http://mrdoob.com/
 */

THREE.XPlaneLinLoader = ( function () {

	var lineRepetition = 3.0;

	function XPlaneLinLoader( manager ) {

		this.manager = ( manager !== undefined ) ? manager : THREE.DefaultLoadingManager;

		this.material = new THREE.MeshLambertMaterial();
		this.material.transparent = true;

	}

	XPlaneLinLoader.prototype = {

		constructor: XPlaneLinLoader,

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

			console.time( 'XPlaneLinLoader' );

			if ( text.indexOf( '\r\n' ) !== - 1 ) {

				// This is faster than String.split with regex that splits on both
				text = text.replace( /\r\n/g, '\n' );

			}

			var lines = text.split( '\n' );
			var line = '';

			// Faster to just trim left side of the line. Use if available.
			var trimLeft = ( typeof ''.trimLeft === 'function' );

			var scaleH, scaleV = 1;
			var textureWidth = -1;
			var layers = [];

			for ( var i = 0, l = lines.length; i < l; i++ ) {

				line = lines[ i ];
				line = trimLeft ? line.trimLeft() : line.trim();

				// Ignore various unimportant lines
				if ( line.length === 0 ) continue;
				if ( line.charAt( 0 ) === '#' || line === 'I' || line === 'A' || line === '850' || line === 'LINE_PAINT' ) continue;

				var data = line.split( /\s+/ );

				switch ( data[ 0 ] ) {

					case 'S_OFFSET':
						// Each S_OFFSET defines a line: <layer> <texture_left> <texture_mid> <texture_right>
						layers.push([ parseInt(data[ 1 ]), parseInt(data[ 2 ]), parseInt(data[ 3 ]), parseInt(data[ 4 ])]);
						break;

					case 'SCALE':
						// Scale defines the size in metres of the texture
						scaleH = parseFloat(data[ 1 ]);
						scaleV = parseFloat(data[ 2 ]);
						break;

					case 'TEX_WIDTH':
						// TEX_WIDTH defines the coordinate space of the texture, irrespective of its real pixel width
						textureWidth = parseFloat(data[ 1 ]);
						break;

					case 'TEXTURE':
						this.loadTexture( this.path + data[ 1 ] );
						break;

					case 'ALIGN':
					case 'END_CAP':
					case 'LAYER_GROUP':
					case 'LOD':
					case 'MIRROR':
					case 'SPECULAR':
					case 'START_CAP':
					case 'TEX_HEIGHT':
					case 'TEXTURE_NORMAL':
						// Lines we are ignoring right now (some may be implemented later)
						break;

					default:

						// Handle null terminated files without exception
						if ( line === '\0' ) continue;

						throw new Error( 'THREE.XPlaneLinLoader: Unexpected line: "' + line + '"' );

				}

			}

			// Sort the layers by the first value in each sub-array (the layer index)
			layers.sort(function(a, b) {
				a = a[1];
				b = b[1];

				return a < b ? -1 : (a > b ? 1 : 0);
			});

			// Calculate the texture scaling factors
			var texScaleH = scaleH / Math.max( scaleH, scaleV );
			var texScaleV = scaleV / Math.max( scaleH, scaleV );

			var container = new THREE.Group();

			// Create underlying surface to provide contrast for our lines
			var geometry = new THREE.BoxGeometry( 1.1, 0.01, 1.1 );
			var material = new THREE.MeshBasicMaterial( { color: 0x008000 } );
			var plane = new THREE.Mesh( geometry, material );
			container.add( plane );

			var currentY = 0.011;
			var scope = this;

			// Create each line, dimensions proportional to loaded texture
			layers.forEach(function( layer ) {
				// Calculate the ratio of this *line's* width to height. The line will use a proportion of the H texture space
				// but always the whole of the V texture space. Therefore we calculate the height part of the ratio by multiplying
				// the H texture space by the *texture's* H:V ratio.
				var lineTextureLeft = layer[ 1 ];
				var lineTextureRight = layer[ 3 ];
				var lineWidth = lineTextureRight - lineTextureLeft;
				var lineHeight = textureWidth * (texScaleV / texScaleH) * lineRepetition;
				var normalisedLineWidth = lineWidth / Math.max( lineWidth, lineHeight ); // 0 to 1
				var normalisedLineHeight = lineHeight / Math.max( lineWidth, lineHeight ); // 0 to 1

				var vertices = [];
				vertices.push( normalisedLineWidth / 2, currentY, normalisedLineHeight / 2 );
				vertices.push( normalisedLineWidth / 2, currentY, -normalisedLineHeight / 2 );
				vertices.push( -normalisedLineWidth / 2, currentY, -normalisedLineHeight / 2 );

				vertices.push( -normalisedLineWidth / 2, currentY, -normalisedLineHeight / 2 );
				vertices.push( -normalisedLineWidth / 2, currentY, normalisedLineHeight / 2 );
				vertices.push( normalisedLineWidth / 2, currentY, normalisedLineHeight / 2 );

				var uvs = [];
				// Convert the coordinates using the TEXTURE_WIDTH coordinate system
				var textureLeft = lineTextureLeft / textureWidth;
				var textureRight = lineTextureRight / textureWidth;
				uvs.push( textureRight, lineRepetition );
				uvs.push( textureRight, 0 );
				uvs.push( textureLeft, 0 );

				uvs.push( textureLeft, 0 );
				uvs.push( textureLeft, lineRepetition );
				uvs.push( textureRight, lineRepetition );

				geometry = new THREE.BufferGeometry( );

				geometry.setAttribute( 'position', new THREE.Float32BufferAttribute( vertices, 3 ) );
				geometry.setAttribute( 'uv', new THREE.Float32BufferAttribute( uvs, 2 ) );
				geometry.computeVertexNormals();
				polygon = new THREE.Mesh( geometry, scope.material );
				container.add( polygon );

				// Each layer should be rendered slightly above the previous one
				currentY += 0.001;
			});

			console.timeEnd( 'XPlaneLinLoader' );

			return container;

		}

	};

	return XPlaneLinLoader;

} )();
