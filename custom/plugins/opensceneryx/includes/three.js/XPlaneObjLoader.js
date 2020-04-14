/**
 * @author aussi / https://github.com/aussig
 * Heavily derived from OBJLoader @author mrdoob / http://mrdoob.com/
 */

THREE.XPlaneObjLoader = ( function () {

	function ParserState() {

		var state = {
			objects: [],
			object: {},

			vertices: [],
			normals: [],
			colors: [],
			uvs: [],
			indices: [],

			material: null,

			startObject: function ( name ) {

				this.object = {
					name: name || '',

					geometry: {
						vertices: [],
						normals: [],
						colors: [],
						uvs: []
					},
					smooth: true,
				};

				this.objects.push( this.object );

			},

			parseVertexIndex: function ( value, len ) {

				return value * 3;

			},

			parseNormalIndex: function ( value, len ) {

				return value * 3;

			},

			parseUVIndex: function ( value, len ) {

				return value * 2;

			},

			addVertex: function ( a, b, c, transx, transy, transz ) {

				var src = this.vertices;
				var dst = this.object.geometry.vertices;

				dst.push( src[ a + 0 ] + transx, src[ a + 1 ] + transy, src[ a + 2 ] + transz );
				dst.push( src[ b + 0 ] + transx, src[ b + 1 ] + transy, src[ b + 2 ] + transz );
				dst.push( src[ c + 0 ] + transx, src[ c + 1 ] + transy, src[ c + 2 ] + transz );

			},

			addNormal: function ( a, b, c ) {

				var src = this.normals;
				var dst = this.object.geometry.normals;

				dst.push( src[ a + 0 ], src[ a + 1 ], src[ a + 2 ] );
				dst.push( src[ b + 0 ], src[ b + 1 ], src[ b + 2 ] );
				dst.push( src[ c + 0 ], src[ c + 1 ], src[ c + 2 ] );

			},

			addColor: function ( a, b, c ) {

				var src = this.colors;
				var dst = this.object.geometry.colors;

				dst.push( src[ a + 0 ], src[ a + 1 ], src[ a + 2 ] );
				dst.push( src[ b + 0 ], src[ b + 1 ], src[ b + 2 ] );
				dst.push( src[ c + 0 ], src[ c + 1 ], src[ c + 2 ] );

			},

			addUV: function ( a, b, c ) {

				var src = this.uvs;
				var dst = this.object.geometry.uvs;

				dst.push( src[ a + 0 ], src[ a + 1 ] );
				dst.push( src[ b + 0 ], src[ b + 1 ] );
				dst.push( src[ c + 0 ], src[ c + 1 ] );

			},

			addFace: function ( a, b, c, ua, ub, uc, na, nb, nc, transx, transy, transz ) {

				var vLen = this.vertices.length;

				var ia = this.parseVertexIndex( a, vLen );
				var ib = this.parseVertexIndex( b, vLen );
				var ic = this.parseVertexIndex( c, vLen );

				this.addVertex( ia, ib, ic, transx, transy, transz );

				if ( ua !== undefined && ua !== '' ) {

					var uvLen = this.uvs.length;
					ia = this.parseUVIndex( ua, uvLen );
					ib = this.parseUVIndex( ub, uvLen );
					ic = this.parseUVIndex( uc, uvLen );
					this.addUV( ia, ib, ic );

				}

				if ( na !== undefined && na !== '' ) {

					// Normals are many times the same. If so, skip function call and parseInt.
					var nLen = this.normals.length;
					ia = this.parseNormalIndex( na, nLen );

					ib = na === nb ? ia : this.parseNormalIndex( nb, nLen );
					ic = na === nc ? ia : this.parseNormalIndex( nc, nLen );

					this.addNormal( ia, ib, ic );

				}

				if ( this.colors.length > 0 ) {

					this.addColor( ia, ib, ic );

				}

			}

		};

		state.startObject( '' );

		return state;

	}

	//

	function XPlaneObjLoader( manager ) {

		this.manager = ( manager !== undefined ) ? manager : THREE.DefaultLoadingManager;

		this.material = new THREE.MeshLambertMaterial();
		// This is a bit naughty. We should really use THREE.BackSide but some animated segments only contain separate sides of the model in different animated sections,
		// which we are ignoring for the moment. Should revert this once animations are implemented.
		this.material.side = THREE.DoubleSide;
		// Set alphaTest to get a fairly good result for transparent textures. Non trivial problem though, see https://threejsfundamentals.org/threejs/lessons/threejs-transparency.html
		this.material.alphaTest = 0.5;
		this.material.transparent = true;

	}

	XPlaneObjLoader.prototype = {

		constructor: XPlaneObjLoader,

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

			THREE.XPlaneUtils.loadTexture(this.material, path);

		},

		parse: function ( text ) {

			console.time( 'XPlaneObjLoader' );

			var state = new ParserState();

			if ( text.indexOf( '\r\n' ) !== - 1 ) {

				// This is faster than String.split with regex that splits on both
				text = text.replace( /\r\n/g, '\n' );

			}

			var lines = text.split( '\n' );
			var line = '';
			var foundLOD = false;
			// Our rudimentary animation approach is draw ONE set of mesh for each animation nesting level, i.e. the first mesh encountered at that level
			// animNest tracks the current nesting level. animTrack is an array of booleans - as we encounter mesh at each nesting level, that index is set to true in the array
			var animNest = 0;
			var animTrack = [];
			// We need to track translations, as in some models animated sections _always_ have some translation. If we don't do this, animated parts may appear in odd places.
			var animTrans = [0, 0, 0];

			// Faster to just trim left side of the line. Use if available.
			var trimLeft = ( typeof ''.trimLeft === 'function' );

			lineloop:
			for ( var i = 0, l = lines.length; i < l; i++ ) {

				line = lines[ i ];

				line = trimLeft ? line.trimLeft() : line.trim();

				// Ignore various unimportant lines
				if ( line.length === 0 ) continue;
				if ( line.charAt( 0 ) === '#' || line === 'I' || line === 'A' || line === '800' || line === 'OBJ' ) continue;

				var data = line.split( /\s+/ );

				switch ( data[ 0 ] ) {

					case 'ANIM_begin':
						animNest++;
						break;

					case 'ANIM_end':
						animNest--;
						// Clear any stored translation
						animTrans = [0, 0, 0];
						break;

					case 'ANIM_trans':
						// There can be multiple ANIM_trans in a single animated block, often used to translate, rotate, then translate back. We therefore need to add the
						// translations together
						animTrans = [ animTrans[ 0 ] + parseFloat(data[ 1 ]), animTrans[ 1 ] + parseFloat(data[ 2 ]), animTrans[ 2 ] + parseFloat(data[ 3 ])];
						break;

					case 'ATTR_LOD':
						// Ignore everything after second LOD. This could be improved to detect the closest LOD or even to be able to select a LOD from the model.
						if ( foundLOD ) break lineloop;
						else foundLOD = true;
						break;

					case 'IDX10':
						for ( var j = 1; j < 11; j++ ) {

							state.indices.push( parseInt( data[ j ] ) );

						}

						break;

					case 'IDX':
						state.indices.push( parseInt( data[ 1 ] ) );
						break;

					case 'LINES':
						// If we already have mesh at this animation nesting level, ignore any further
						if (animNest > 0 && animTrack[animNest]) break;
						// Ignore any further animated mesh at this level
						if (animNest > 0) animTrack[animNest] = true;

						// TODO: Implement lines
						break;

					case 'TEXTURE':
						this.loadTexture( this.path + data[ 1 ] );
						break;

					case 'TRIS':
						// If we already have mesh at this animation nesting level, ignore any further
						if (animNest > 0 && animTrack[animNest]) break;
						// Ignore any further animated mesh at this level
						if (animNest > 0) animTrack[animNest] = true;

						// Build our face data from the number of tris specified
						var index = parseInt( data[ 1 ] );
						var count = parseInt( data[ 2 ] );

						for ( var j = index; j < index + count; j += 3 ) {

							// The vertex, uv and normal arrays are always perfectly aligned, so use same indices into each
							state.addFace(
								state.indices[ j ], state.indices[ j + 1 ], state.indices[ j + 2 ],
								state.indices[ j ], state.indices[ j + 1 ], state.indices[ j + 2 ],
								state.indices[ j ], state.indices[ j + 1 ], state.indices[ j + 2 ],
								animTrans[ 0 ], animTrans[ 1 ], animTrans[ 2 ]
							);

						}

						break;

					case 'VLINE':
						// TODO: Implement lines
						break;

					case 'VT':
						state.vertices.push(
							parseFloat( data[ 1 ] ),
							parseFloat( data[ 2 ] ),
							parseFloat( data[ 3 ] )
						);
						state.normals.push(
							parseFloat( data[ 4 ] ),
							parseFloat( data[ 5 ] ),
							parseFloat( data[ 6 ] )
						);
						state.uvs.push(
							parseFloat( data[ 7 ] ),
							parseFloat( 1.0 - data[ 8 ] )
						);
						/*state.colors.push(
							Math.random(),
							Math.random(),
							Math.random()
						);*/
						break;

					case 'ANIM_hide':
					case 'ANIM_keyframe_loop':
					case 'ANIM_rotate':
					case 'ANIM_rotate_begin':
					case 'ANIM_rotate_end':
					case 'ANIM_rotate_key':
					case 'ANIM_show':
					case 'ANIM_trans_begin':
					case 'ANIM_trans_end':
					case 'ANIM_trans_key':
					case 'ATTR_ambient_rgb':
					case 'ATTR_axis_detent_range':
					case 'ATTR_axis_detented':
					case 'ATTR_blend':
					case 'ATTR_cockpit':
					case 'ATTR_cockpit_device':
					case 'ATTR_cockpit_region':
					case 'ATTR_cull':
					case 'ATTR_depth':
					case 'ATTR_diffuse_rgb':
					case 'ATTR_draped':
					case 'ATTR_draw_disable':
					case 'ATTR_draw_enable':
					case 'ATTR_emission_rgb':
					case 'ATTR_hard':
					case 'ATTR_hard_deck':
					case 'ATTR_layer_group':
					case 'ATTR_layer_group_draped':
					case 'ATTR_light_level':
					case 'ATTR_light_level_reset':
					case 'ATTR_LOD_draped':
					case 'ATTR_manip_axis_knob':
					case 'ATTR_manip_axis_switch_left_right':
					case 'ATTR_manip_axis_switch_up_down':
					case 'ATTR_manip_command':
					case 'ATTR_manip_command_axis':
					case 'ATTR_manip_command_knob':
					case 'ATTR_manip_command_knob2':
					case 'ATTR_manip_command_switch_left_right':
					case 'ATTR_manip_command_switch_left_right2':
					case 'ATTR_manip_command_switch_up_down':
					case 'ATTR_manip_command_switch_up_down2':
					case 'ATTR_manip_delta':
					case 'ATTR_manip_drag_axis':
					case 'ATTR_manip_drag_axis_pix':
					case 'ATTR_manip_drag_rotate':
					case 'ATTR_manip_drag_xy':
					case 'ATTR_manip_keyframe':
					case 'ATTR_manip_none':
					case 'ATTR_manip_noop':
					case 'ATTR_manip_push':
					case 'ATTR_manip_radio':
					case 'ATTR_manip_toggle':
					case 'ATTR_manip_wheel':
					case 'ATTR_manip_wrap':
					case 'ATTR_no_blend':
					case 'ATTR_no_cockpit':
					case 'ATTR_no_cull':
					case 'ATTR_no_depth':
					case 'ATTR_no_draped':
					case 'ATTR_no_hard':
					case 'ATTR_no_shadow':
					case 'ATTR_no_solid_camera':
					case 'ATTR_poly_os':
					case 'ATTR_reset':
					case 'ATTR_shade_flat':
					case 'ATTR_shade_smooth':
					case 'ATTR_shadow':
					case 'ATTR_shadow_blend':
					case 'ATTR_shiny_rat':
					case 'ATTR_solid_camera':
					case 'ATTR_specular_rgb':
					case 'BUMP_LEVEL':
					case 'COCKPIT_REGION':
					case 'DECAL':
					case 'DECAL_KEYED':
					case 'DECAL_LIB':
					case 'DECAL_PARAMS':
					case 'DECAL_PARAMS_PROJ':
					case 'DECAL_RGBA':
					case 'DITHER_ALPHA':
					case 'IF':
					case 'ELSE':
					case 'EMITTER':
					case 'ENDIF':
					case 'GLOBAL_cockpit_lit':
					case 'GLOBAL_no_blend':
					case 'GLOBAL_no_shadow':
					case 'GLOBAL_shadow_blend':
					case 'GLOBAL_specular':
					case 'GLOBAL_tint':
					case 'LIGHT_CUSTOM':
					case 'LIGHT_NAMED':
					case 'LIGHT_PARAM':
					case 'LIGHT_SPILL_CUSTOM':
					case 'LIGHTS':
					case 'MAGNET':
					case 'NO_ALPHA':
					case 'NO_BLEND':
					case 'NO_SHADOW':
					case 'PARTICLE_SYSTEM':
					case 'POINT_COUNTS':
					case 'REQUIRE_WET':
					case 'REQUIRE_DRY':
					case 'SLOPE_LIMIT':
					case 'slung_load_weight':
					case 'smoke_black':
					case 'smoke_white':
					case 'SPECULAR':
					case 'TEXTURE_CONTROL':
					case 'TEXTURE_DETAIL':
					case 'TEXTURE_LIT':
					case 'TEXTURE_LIT_NOWRAP':
					case 'TEXTURE_NORMAL':
					case 'TEXTURE_NORMAL_NOWRAP':
					case 'TEXTURE_NOWRAP':
					case 'TEXTURE_TILE':
					case 'TILTED':
					case 'TWO_SIDED':
					case 'VLIGHT':
						// Lines we are ignoring right now (some may be implemented later)
						break;

					default:

						// Handle null terminated files without exception
						if ( line === '\0' ) continue;

						throw new Error( 'THREE.XPlaneObjLoader: Unexpected line: "' + line + '"' );

				}

			}

			var container = new THREE.Group();

			for ( var i = 0, l = state.objects.length; i < l; i ++ ) {

				var object = state.objects[ i ];
				var geometry = object.geometry;
				var material = this.material;

				if ( geometry.vertices.length === 0 ) continue;

				var buffergeometry = new THREE.BufferGeometry();

				buffergeometry.setAttribute( 'position', new THREE.Float32BufferAttribute( geometry.vertices, 3 ) );

				if ( geometry.normals.length > 0 ) {

					buffergeometry.setAttribute( 'normal', new THREE.Float32BufferAttribute( geometry.normals, 3 ) );

				} else {

					buffergeometry.computeVertexNormals();

				}

				if ( geometry.colors.length > 0 ) {

					hasVertexColors = true;
					buffergeometry.setAttribute( 'color', new THREE.Float32BufferAttribute( geometry.colors, 3 ) );

				}

				if ( geometry.uvs.length > 0 ) {

					buffergeometry.setAttribute( 'uv', new THREE.Float32BufferAttribute( geometry.uvs, 2 ) );

				}

				// Create mesh
				var mesh = new THREE.Mesh( buffergeometry, material );
				mesh.name = object.name;
				container.add( mesh );

			}

			console.timeEnd( 'XPlaneObjLoader' );

			return container;

		}

	};

	return XPlaneObjLoader;

} )();
