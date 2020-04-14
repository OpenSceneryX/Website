
THREE.XPlaneUtils = {

    textureModes: {
        STANDARD: 0,
        NORMAL: 1
    },

	loadTexture: function ( material, path, wrap = false, mode = THREE.XPlaneUtils.textureModes.STANDARD ) {

        var scope = this;
        var textureLoader = new THREE.TextureLoader();
        var ddsLoader = new THREE.DDSLoader();

        // Spec says that even if the specified texture has a .png suffix, X-Plane will attempt to load a DDS
        // texture with equivalent .dds extension first. However, DDS textures are not supported on many mobile
        // devices so we load the .png first.

        var splitPath = path.split('.');
        // Remove extension
        splitPath.pop();

        var ddsPath = splitPath.concat(['dds']).join('.');
        var pngPath = splitPath.concat(['png']).join('.');

        textureLoader.load(
            // resource URL
            pngPath,

            // onLoad callback
            function ( texture ) {
                scope.textureLoaded(texture, material, wrap, mode);
            },

            // onProgress callback
            undefined,

            // onError callback
            function ( err ) {
                ddsLoader.load(
                    // resource URL
                    ddsPath,

                    // onLoad callback
                    function ( texture ) {
                        scope.textureLoaded(texture, material, wrap, mode);
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
    },

    textureLoaded: function ( texture, material, wrap, mode ) {

        texture.flipY = false; // Never flip texture because DDS compressed textures are never flipped and we can't detect or change that due to https://github.com/mrdoob/three.js/issues/4316
        texture.anisotropy = 16;

        if (wrap) texture.wrapT = THREE.RepeatWrapping;

        if (mode == THREE.XPlaneUtils.textureModes.STANDARD) {
            material.map = texture;
            material.map.needsUpdate = true;
        } else if (mode == THREE.XPlaneUtils.textureModes.NORMAL) {
            material.normalMap = texture;
            material.normalMap.needsUpdate = true;
        }

        material.needsUpdate = true;
    },
};
