/**
 * @author aussi / https://github.com/aussig
 * Heavily derived from OBJLoader @author mrdoob / http://mrdoob.com/
 */

THREE.XPlaneForLoader = ( function () {

	var treesPerRow = 6;

	function XPlaneForLoader( manager ) {

		this.manager = ( manager !== undefined ) ? manager : THREE.DefaultLoadingManager;

		this.material = new THREE.MeshLambertMaterial();
		this.material.side = THREE.DoubleSide;
		// Set alphaTest to get a good result for transparent textures. See https://threejsfundamentals.org/threejs/lessons/threejs-transparency.html
		this.material.alphaTest = 0.5;
		this.material.transparent = true;

	}

	XPlaneForLoader.prototype = {

		constructor: XPlaneForLoader,

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

			var scope = this;
			var textureLoader = new THREE.TextureLoader();
			var ddsLoader = new THREE.DDSLoader();

			// Spec says that even if the specified texture has a .png suffix, X-Plane will attempt to load a DDS
			// texture with equivalent .dds extension first. So we do the same.
			var splitPath = path.split('.');
			// Remove extension
			splitPath.pop();

			var ddsPath = splitPath.concat(['dds']).join('.');
			var pngPath = splitPath.concat(['png']).join('.');

			ddsLoader.load(
				// resource URL
				ddsPath,

				// onLoad callback
				function ( texture ) {
					texture.anisotropy = 16;
					scope.material.map = texture;
					scope.material.map.needsUpdate = true;
					scope.material.needsUpdate = true;
				},

				// onProgress callback
				undefined,

				// onError callback
				function ( err ) {
					textureLoader.load(
						// resource URL
						pngPath,

						// onLoad callback
						function ( texture ) {
							texture.anisotropy = 16;
							scope.material.map = texture;
							scope.material.map.needsUpdate = true;
							scope.material.needsUpdate = true;
						},

						// onProgress callback
						undefined,

						// onError callback
						function ( err ) {
							console.error( 'Could not load texture. Tried ' + ddsPath + ' and ' + pngPath );
						}
					);
				}
			);

			return this;

		},

		parse: function ( text ) {

			console.time( 'XPlaneForLoader' );

			if ( text.indexOf( '\r\n' ) !== - 1 ) {

				// This is faster than String.split with regex that splits on both
				text = text.replace( /\r\n/g, '\n' );

			}

			var lines = text.split( '\n' );
			var line = '';

			// Faster to just trim left side of the line. Use if available.
			var trimLeft = ( typeof ''.trimLeft === 'function' );

			var scaleX, scaleY = 256;
			var spacingX, spacingZ = 30;
			var randomX, randomZ = 0;
			var trees = [];

			for ( var i = 0, l = lines.length; i < l; i++ ) {

				line = lines[ i ];
				line = trimLeft ? line.trimLeft() : line.trim();

				// Ignore various unimportant lines
				if ( line.length === 0 ) continue;
				if ( line.charAt( 0 ) === '#' || line === 'I' || line === 'A' || line === '800' || line === 'FOREST' ) continue;

				var data = line.split( /\s+/ );

				switch ( data[ 0 ] ) {

					case 'RANDOM':
						randomX = parseFloat(data[ 1 ]);
						randomZ = parseFloat(data[ 2 ]);
						break;

					case 'SCALE_X':
						scaleX = parseFloat(data[ 1 ]);
						break;

					case 'SCALE_Y':
						scaleY = parseFloat(data[ 1 ]);
						break;

					case 'SPACING':
						spacingX = parseFloat(data[ 1 ]);
						spacingZ = parseFloat(data[ 2 ]);
						break;

					case 'TEXTURE':
						this.loadTexture( this.path + data[ 1 ] );
						break;

					case 'TREE':
						// Each TREE defines a tree: <s> <t> <w> <y> <offset> <frequency> <min height> <max height> <quads> <type>
						trees.push([ parseInt(data[ 1 ]), parseInt(data[ 2 ]), parseInt(data[ 3 ]), parseInt(data[ 4 ]), parseInt(data[ 5 ]), parseInt(data[ 6 ]), parseInt(data[ 7 ]), parseInt(data[ 8 ]), parseInt(data[ 9 ]), parseInt(data[ 10 ])]);
						break;

					case 'GROUP':
					case 'LOD':
					case 'SKIP_SURFACE':
					case 'Y_QUAD':
						// Really need to support this
					case 'CHOICE_PARAMS':
						// Really need to support this
					case 'DENSITY_PARAMS':
						// Really need to support this
					case 'HEIGHT_PARAMS':
						// Really need to support this
					case 'NO_SHADOW':
						// Lines we are ignoring right now (some may be implemented later)
						break;

					default:

						// Handle null terminated files without exception
						if ( line === '\0' ) continue;

						throw new Error( 'THREE.XPlaneForLoader: Unexpected line: "' + line + '"' );

				}

			}

			// Build an array of trees we want to plant, populated using the correct percentages for each
			// Adapted from WED_ResourceMgr::GetFor() in /src/WEDCore/WED_ResourceMgr.cpp from xptools
			var species = [];
			var varieties = trees.length;
			var plantedTrees = [];
			for ( var i = 0; i < treesPerRow * treesPerRow; i++) species.push(0);

			for ( var i = varieties - 1; i > 0; i-- ) {
				for ( var j = 0; j < Math.round( trees[i][5] / 100.0 * treesPerRow * treesPerRow ); j++ ) {
					var cnt = 10;     // needed in case the tree percentages add up to more than 100%
					do {
						var where = Math.round( treesPerRow * treesPerRow * Math.random() );
						if ( !species[where] ) {
							species[where] = i;
							break;
						}
					} while (cnt--);
				}
			}
			for ( var i = 0; i < treesPerRow * treesPerRow; i++)
				plantedTrees.push( trees[species[i]] );

			console.log(plantedTrees);


			var container = new THREE.Group();

			// Create underlying surface to provide contrast for our trees
			var geometry = new THREE.BoxGeometry( 1.5, 0.01, 1.5 );
			var material = new THREE.MeshBasicMaterial( { color: 0x00aa00 } );
			var plane = new THREE.Mesh( geometry, material );
			container.add( plane );

			// Create trees, dimensions proportional to loaded texture
			for ( var i = 0; i < plantedTrees.length; i++ ) {
				this.addTree( container, plantedTrees[i], i, spacingX, spacingZ, randomX, randomZ, scaleX, scaleY );
			}

			//var geometry = new THREE.PlaneGeometry( scaleX / Math.max( scaleX, scaleY ), scaleY / Math.max( scaleX, scaleY ) );
			//var tree = new THREE.Mesh( geometry, this.material );
			//container.add( tree );
			//tree.translateY( 0.001 );

			console.timeEnd( 'XPlaneForLoader' );

			return container;

		},

		addTree: function ( container, treeData, index, spacingX, spacingZ, randomX, randomZ, scaleX, scaleY ) {
			// treeData: <s> <t> <w> <y> <offset> <frequency> <min height> <max height> <quads> <type>
			var treeH = treeData[6] + Math.random() * ( treeData[7] - treeData[6] );
			var treeW = treeH * treeData[2] / treeData[3];
			var treeX = ( index % treesPerRow ) * spacingX + randomX * ( 2.0 * Math.random() - 1.0 );
			var treeZ = ( index / treesPerRow ) * spacingZ + randomZ * ( 2.0 * Math.random() - 1.0 );
			var treeRotation = Math.random();

			var vertices = [];
			var normals = [];
			var uvs = [];

			var scope = this

			for ( var i = 0; i < treeData[8]; i++ ) {
				var quadRotation = Math.PI * ( treeRotation + i / treeData[8] );        // tree rotation
				var quadX = treeW * Math.sin( quadRotation );
				var quadZ = treeW * Math.cos( quadRotation );

				// Tri 1
				vertices.push( treeX - quadX * ( treeData[4] / treeData[2] ), 0.0, treeZ - quadZ * ( treeData[4] / treeData[2] ) );
				normals.push( 0.0, 1.0, 0.0 );
				uvs.push( treeData[0] / scaleX, 1 - treeData[1] / scaleY );

				vertices.push( treeX + quadX * ( 1.0 - treeData[4] / treeData[2] ), 0.0, treeZ + quadZ * ( 1.0 - treeData[4] / treeData[2] ) );
				normals.push( 0.0, 1.0, 0.0 );
				uvs.push( ( treeData[0] + treeData[2] ) / scaleX, 1 - treeData[1] / scaleY );

				vertices.push( treeX + quadX * ( 1.0 - treeData[4] / treeData[2] ), treeH, treeZ + quadZ * ( 1.0 - treeData[4] / treeData[2] ) );
				normals.push( 0.0, 1.0, 0.0 );
				uvs.push( ( treeData[0] + treeData[2] ) / scaleX, 1 - ( treeData[1] + treeData[3] ) / scaleY );

				// Tri 2
				vertices.push( treeX - quadX * ( treeData[4] / treeData[2] ), 0.0, treeZ - quadZ * ( treeData[4] / treeData[2] ) );
				normals.push( 0.0, 1.0, 0.0 );
				uvs.push( treeData[0] / scaleX, 1 - treeData[1] / scaleY );

				vertices.push( treeX + quadX * ( 1.0 - treeData[4] / treeData[2] ), treeH, treeZ + quadZ * ( 1.0 - treeData[4] / treeData[2] ) );
				normals.push( 0.0, 1.0, 0.0 );
				uvs.push( ( treeData[0] + treeData[2] ) / scaleX, 1 - ( treeData[1] + treeData[3] ) / scaleY );

				vertices.push( treeX - quadX * (treeData[4] / treeData[2] ), treeH, treeZ - quadZ * ( treeData[4] / treeData[2] ) );
				normals.push( 0.0, 1.0, 0.0 );
				uvs.push( treeData[0] / scaleX, 1 - ( treeData[1] + treeData[3] ) / scaleY );
			}

			var geometry = new THREE.BufferGeometry( );
			geometry.addAttribute( 'position', new THREE.Float32BufferAttribute( vertices, 3 ) );
			geometry.addAttribute( 'uv', new THREE.Float32BufferAttribute( uvs, 2 ) );
			geometry.addAttribute( 'normal', new THREE.Float32BufferAttribute( normals, 3 ) );

			var polygon = new THREE.Mesh( geometry, scope.material );
			container.add( polygon );
		}

	};

	return XPlaneForLoader;

} )();
