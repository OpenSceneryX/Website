
THREE.XPlaneUtils = {

    textureModes: {
        STANDARD: 0,
        NORMAL: 1
    },

	loadTexture: function ( material, path, wrap = false, mode = THREE.XPlaneUtils.textureModes.STANDARD ) {

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

            // onProgress callback
            undefined,

            // onError callback
            function ( err ) {
                textureLoader.load(
                    // resource URL
                    pngPath,

                    // onLoad callback
                    function ( texture ) {
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
};
