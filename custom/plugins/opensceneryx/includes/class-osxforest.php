<?php

/**
 * Description of OSXObject
 */
class OSXForest extends OSXLibraryItem {

    protected $spacingX = null;
    protected $spacingZ = null;
    protected $randomX = null;
    protected $randomZ = null;
    protected $skipSurfaces = null;

    protected $group = false;
    protected $perlin = false;

    protected $lod = null;

    function __construct($path, $url) {
        parent::__construct($path, $url);
    }

    function enqueueScript() {
        parent::enqueueScript();

        $threejsScript = '<script type="text/javascript">
            $(document).ready(function(){
                var scene = new THREE.Scene();
                var container = $(".threejs-container")
                var camera = new THREE.PerspectiveCamera( 75, container.width() / container.height(), 0.1, 1000 );
                var renderer = new THREE.WebGLRenderer();

                renderer.setSize(container.width(), container.height());
                container.append( renderer.domElement );
                scene.background = new THREE.Color(0xffffff);

                var controls = new THREE.OrbitControls(camera, renderer.domElement);
                controls.autoRotate = true;

                var skyLight = new THREE.HemisphereLight( 0xffffbb, 0x080820, 1 );
                var ambientLight = new THREE.AmbientLight( 0x404040 );
                var sunLight = new THREE.DirectionalLight(0xdddddd, 1.5);
                sunLight.position.set(1000, -1000, 1000);

                scene.add(skyLight);
                scene.add(ambientLight);
                scene.add(sunLight);

                var forLoader = new THREE.XPlaneForLoader();
                forLoader.setPath("' . DOWNLOADS_DOMAIN . '/library/' . dirname($this->filePath) . '/");

                forLoader.load("' . basename($this->filePath) . '", function (object) {
                    scene.add(object);

                    // Dynamically determine the bounding box and set the camera distance accordingly
                    var bBox = new THREE.Box3().setFromObject(object);
                    var bBoxSize = new THREE.Vector3();
                    var bBoxCenter = new THREE.Vector3();

                    bBox.getSize(bBoxSize);
                    bBox.getCenter(bBoxCenter);

                    // Center object in scene.
                    object.translateX(-bBoxCenter.x);
                    object.translateY(-bBoxCenter.y);
                    object.translateZ(-bBoxCenter.z);

                    // Calculate the camera distance based on the maximum dimensions of the model
                    var dist = Math.max(bBoxSize.x, bBoxSize.y, bBoxSize.z) / (2 * Math.tan(camera.fov * Math.PI / 360));
                    var pos = scene.position;
                    camera.position.set(pos.x, pos.y, dist * 1.7);
                    camera.lookAt(pos);
                });

                var animate = function () {
                    requestAnimationFrame( animate );
                    controls.update();
                    renderer.render(scene, camera);
                };

                animate();
            });
            </script>';

        wp_enqueue_script('3xpforloader', plugin_dir_url(__FILE__) . 'three.js/XPlaneForLoader.js', array('three.js', '3ddsloader'), false, true);
        wp_add_inline_script('3xpforloader', $threejsScript, 'after');
    }

    protected function parse() {
        parent::parse();

        $matches = array();

        foreach ($this->fileLines as $line) {
            if (preg_match('/^Spacing X:\s+(.*)/', $line, $matches) === 1) {
                $this->spacingX = $matches[1];
                continue;
            }

            if (preg_match('/^Spacing Z:\s+(.*)/', $line, $matches) === 1) {
                $this->spacingZ = $matches[1];
                continue;
            }

            if (preg_match('/^Random X:\s+(.*)/', $line, $matches) === 1) {
                $this->randomX = $matches[1];
                continue;
            }

            if (preg_match('/^Random Z:\s+(.*)/', $line, $matches) === 1) {
                $this->randomZ = $matches[1];
                continue;
            }

            if (preg_match('/^Skip Surfaces:\s+(.*)/', $line, $matches) === 1) {
                $this->skipSurfaces = $matches[1];
                continue;
            }

            if (preg_match('/^Group:\s+(.*)/', $line, $matches) === 1) {
                $this->group = ($matches[1] == "True" || $matches[1] == "Yes");
                continue;
            }

            if (preg_match('/^Perlin:\s+(.*)/', $line, $matches) === 1) {
                $this->perlin = ($matches[1] == "True" || $matches[1] == "Yes");
                continue;
            }

            if (preg_match('/^LOD:\s+(.*)/', $line, $matches) === 1) {
                $this->lod = $matches[1];
                continue;
            }
        }
    }

    protected function getTypeSpecificHTML() {
        $result = "";

        if ($this->spacingX !== null && $this->spacingZ !== null) {
            $result .= "<li><span class='fieldTitle'>Spacing X / Z </span><dfn class='tooltip'>ⓘ<span>The items in this forest use " . self::dimension($this->spacingX, self::UNITS_METRES) . " (" . self::dimension($this->spacingX, self::UNITS_FEET) . ") spacing in the X dimension and " . self::dimension($this->spacingZ, self::UNITS_METRES) . " (" . self::dimension($this->spacingZ, self::UNITS_FEET) . ") spacing in the Z dimension. For more information, <a href='https://developer.x-plane.com/article/forest-for-file-format-specification/#SPACING_ltx_spacinggt_ltz_spacinggt' target='_blank'>see the official documentation</a>.</span></dfn>: <span class='fieldValue'>" . self::dimension($this->spacingX, self::UNITS_METRES) . " / " . self::dimension($this->spacingZ, self::UNITS_METRES) . " (" . self::dimension($this->spacingX, self::UNITS_FEET). " / " . self::dimension($this->spacingZ, self::UNITS_FEET) . ")</span></li>\n";
        }

        if ($this->randomX !== null && $this->randomZ !== null) {
            $result .= "<li><span class='fieldTitle'>Random X / Z </span><dfn class='tooltip'>ⓘ<span>The items in this forest can deviate from the spacing by up to " . self::dimension($this->randomX, self::UNITS_METRES) . " (" . self::dimension($this->randomX, self::UNITS_FEET) . ") in the X dimension and " . self::dimension($this->randomZ, self::UNITS_METRES) . " (" . self::dimension($this->randomZ, self::UNITS_FEET) . ") in the Z dimension. For more information, <a href='https://developer.x-plane.com/article/forest-for-file-format-specification/#RANDOM_ltx_spacinggt_ltz_spacinggt' target='_blank'>see the official documentation</a>.</span></dfn>: <span class='fieldValue'>" . self::dimension($this->randomX, self::UNITS_METRES) . " / " . self::dimension($this->randomZ, self::UNITS_METRES) . " (" . self::dimension($this->randomX, self::UNITS_FEET). " / " . self::dimension($this->randomZ, self::UNITS_FEET) . ")</span></li>\n";
        }

        if ($this->skipSurfaces !== null) {
            $result .= "<li><span class='fieldTitle'>Skip Surfaces </span><dfn class='tooltip'>ⓘ<span>X-Plane® will not place this forest on " . $this->skipSurfaces . " surfaces. For more information, <a href='https://developer.x-plane.com/article/forest-for-file-format-specification/#SKIP_SURFACE_ltsurface_typegt' target='_blank'>see the official documentation</a>.</span></dfn>: <span class='fieldValue'>" . $this->skipSurfaces . "</span></li>\n";
        }

        if ($this->group) {
            $result .= "<li><span class='fieldTitle'>Contains Forest Groups</span> <dfn class='tooltip'>ⓘ<span>Forest groups are used to create clusters of differing trees within a single forest. For more information, <a href='https://developer.x-plane.com/article/forest-for-file-format-specification/#GROUP_layer_percent' target='_blank'>see the official documentation</a>.</span></dfn></li>\n";
        }

        if ($this->perlin) {
            $result .= "<li><span class='fieldTitle'>Contains Perlin Noise Randomisation</span> <dfn class='tooltip'>ⓘ<span>This forest uses Perlin noise to distribute density, tree choice or height. For more information, <a href='https://developer.x-plane.com/article/forest-for-file-format-specification/#DENSITY_PARAMS_ltperlin_paramsgt' target='_blank'>see the official documentation</a>.</span></dfn></li>\n";
        }

        if ($this->lod !== null) {
            $result .= "<li><span class='fieldTitle'>LOD Range </span><dfn class='tooltip'>ⓘ<span>This forest is drawn up to a distance of " . self::dimension($this->lod, self::UNITS_METRES) . " (" . self::dimension($this->lod, self::UNITS_MILES) . ") from the user. For more information, <a href='https://developer.x-plane.com/article/forest-for-file-format-specification/#LOD_ltmax_lodgt' target='_blank'>see the official documentation</a>.</span></dfn>: <span class='fieldValue'>" . self::dimension($this->lod, self::UNITS_METRES) . " (" . self::dimension($this->lod, self::UNITS_MILES) . ")</span></li>\n";
        }

        if ($result != "") {
            $result = "<h2>Forest-specific Details</h2><ul>\n" . $result . "</ul>\n";
        }

        return $result;
    }
}
