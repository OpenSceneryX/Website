<?php

/**
 * Description of OSXObject
 */
class OSXObject extends OSXLibraryItem {
    const FILENAME_ROOT = "object";
    const FILENAME_EXT= "obj";

    protected $width = null;
    protected $height = null;
    protected $depth = null;

    protected $animated = false;

    protected $lods = array();

    protected $lightsCustom = false;
    protected $lightsNamed = false;
    protected $lightsParameterised = false;
    protected $lightsSpill = false;

    protected $tilted = false;
    protected $smokeBlack = false;
    protected $smokeWhite = false;

    protected $wedRotationLockAngle = null;

    protected $samStaticAircraftDoors = 0;

    function __construct($path, $url) {
        parent::__construct($path, $url, 'object');
    }

    function enqueueScript() {
        parent::enqueueScript();

        $threejsScript = '<script type="text/javascript">
            var currentObject = null;
            var scene = null;
            var camera = null;

            $(document).ready(function(){
                $(".season-button").click(function() {
                    if ($(this).attr("id") == "summer") var fileName = "' . self::FILENAME_ROOT . '.' . self::FILENAME_EXT . '";
                    else var fileName = "' . self::FILENAME_ROOT . '_" + $(this).attr("id") + ".' . self::FILENAME_EXT . '";
                    load3dPreview("' . DOWNLOADS_DOMAIN . '/library/' . dirname($this->filePath) . '/", fileName);
                });

                scene = new THREE.Scene();
                var container = $(".threejs-container")
                camera = new THREE.PerspectiveCamera( 75, container.width() / container.height(), 0.1, 1000 );
                var renderer = new THREE.WebGLRenderer();

                renderer.setSize(container.width(), container.height());
                container.append( renderer.domElement );
                scene.background = new THREE.Color(0xffffff);

                var controls = new THREE.OrbitControls(camera, renderer.domElement);
                controls.autoRotate = true;

                var skyLight = new THREE.HemisphereLight( 0xffffff, 0x080808, 0.7 );
                var ambientLight = new THREE.AmbientLight( 0x404040 );
                var sunLight = new THREE.DirectionalLight(0xdddddd, 1.5);
                sunLight.position.set(1000, -1000, 1000);

                scene.add(skyLight);
                scene.add(ambientLight);
                scene.add(sunLight);

                load3dPreview("' . DOWNLOADS_DOMAIN . '/library/' . dirname($this->filePath) . '/", "' . basename($this->filePath) . '");

                var animate = function () {
                    requestAnimationFrame( animate );
                    controls.update();
                    renderer.render(scene, camera);
                };

                animate();
            });

            function load3dPreview(urlBase, fileName) {
                var objLoader = new THREE.XPlaneObjLoader();
                objLoader.setPath(urlBase);

                objLoader.load(fileName, function (object) {
                    if (currentObject) scene.remove(currentObject);
                    scene.add(object);
                    currentObject = object;

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
            }
            </script>';

        wp_enqueue_script('3xpobjloader', plugin_dir_url(__FILE__) . 'three.js/XPlaneObjLoader.js', array('three.js', '3ddsloader'), false, true);
        wp_add_inline_script('3xpobjloader', $threejsScript, 'after');
    }

    protected function parse() {
        parent::parse();

        $matches = array();

        foreach ($this->fileLines as $line) {
            if (preg_match('/^Width:\s+(.*)/', $line, $matches) === 1) {
                $this->width = $matches[1];
                continue;
            }

            if (preg_match('/^Height:\s+(.*)/', $line, $matches) === 1) {
                $this->height = $matches[1];
                continue;
            }

            if (preg_match('/^Depth:\s+(.*)/', $line, $matches) === 1) {
                $this->depth = $matches[1];
                continue;
            }

            if (preg_match('/^Animated:\s+(.*)/', $line, $matches) === 1) {
                $this->animated = ($matches[1] == "True" || $matches[1] == "Yes");
                continue;
            }

            if (preg_match('/^LOD:\s+(.*?)\s+(.*)/', $line, $matches) === 1) {
                $this->lods[] = array('min' => $matches[1], 'max' => $matches[2]);
                continue;
            }

            if (preg_match('/^Custom Lights:\s+(.*)/', $line, $matches) === 1) {
                $this->lightsCustom = ($matches[1] == "True" || $matches[1] == "Yes");
                continue;
            }

            if (preg_match('/^Named Lights:\s+(.*)/', $line, $matches) === 1) {
                $this->lightsNamed = ($matches[1] == "True" || $matches[1] == "Yes");
                continue;
            }

            if (preg_match('/^Parameterised Lights:\s+(.*)/', $line, $matches) === 1) {
                $this->lightsParameterised = ($matches[1] == "True" || $matches[1] == "Yes");
                continue;
            }

            if (preg_match('/^Spill Lights:\s+(.*)/', $line, $matches) === 1) {
                $this->lightsSpill = ($matches[1] == "True" || $matches[1] == "Yes");
                continue;
            }

            if (preg_match('/^Tilted:\s+(.*)/', $line, $matches) === 1) {
                $this->tilted = ($matches[1] == "True" || $matches[1] == "Yes");
                continue;
            }

            if (preg_match('/^Black Smoke:\s+(.*)/', $line, $matches) === 1) {
                $this->smokeBlack = ($matches[1] == "True" || $matches[1] == "Yes");
                continue;
            }

            if (preg_match('/^White Smoke:\s+(.*)/', $line, $matches) === 1) {
                $this->smokeWhite = ($matches[1] == "True" || $matches[1] == "Yes");
                continue;
            }

            if (preg_match('/^Rotation Lock:\s+(.*)/', $line, $matches) === 1) {
                $this->wedRotationLockAngle = $matches[1];
                continue;
            }

            if (preg_match('/^SAM Static Aircraft:\s+(.*)\s+(.*)\s+(.*)\s+(.*)\s+(.*)/', $line, $matches) === 1) {
                $this->samStaticAircraftDoors++;
                continue;
            }

        }
    }

    protected function getTypeSpecificHTML() {
        $result = "";

        if ($this->width !== null && $this->height !== null && $this->depth !== null) {
            $result .= "<li><span class='fieldTitle'>Dimensions</span> <dfn class='tooltip'>ⓘ<span>These are the outermost bounds of the object.</span></dfn>:\n";
            $result .= "<ul class='dimensions'>\n";
            $result .= "<li id='width'><span class='fieldTitle'>w:</span> " . self::dimension($this->width, self::UNITS_METRES) . " (" . self::dimension($this->width, self::UNITS_FEET) . ")</li>\n";
            $result .= "<li id='height'><span class='fieldTitle'>h:</span> " . self::dimension($this->height, self::UNITS_METRES) . " (" . self::dimension($this->height, self::UNITS_FEET) . ")</li>\n";
            $result .= "<li id='depth'><span class='fieldTitle'>d:</span> " . self::dimension($this->depth, self::UNITS_METRES) . " (" . self::dimension($this->depth, self::UNITS_FEET) . ")</li>\n";
            $result .= "</ul>\n";
            $result .= "</li>\n";
        }

        if ($this->animated) {
            $result .= "<li class='animated'><span class='fieldValue'>Animated</span> <dfn class='tooltip'>ⓘ<span>This object contains animation.</span></dfn></li>\n";
        }

        if (count($this->lods) > 0) {
            foreach ($this->lods as $lod) {
                $result .= "<li><span class='fieldTitle'>LOD Range</span> <dfn class='tooltip'>ⓘ<span>This object contains multiple Levels of Detail (LODs). To improve frame rate, simpler models are used at further distances. For more information, <a href='https://developer.x-plane.com/article/obj8-file-format-specification/#ATTR_LOD_ltneargt_ltfargt' target='_blank'>see the official documentation</a>.</span></dfn>: <span class='fieldValue'>" . self::dimension($lod['min'], self::UNITS_METRES) . " to " . self::dimension($lod['max'], self::UNITS_METRES) . " (" . self::dimension($lod['min'], self::UNITS_MILES) . " to " . self::dimension($lod['max'], self::UNITS_MILES) . ")</span></li>\n";
            }
        }

        if ($this->lightsCustom) {
            $result .= "<li><span class='fieldValue'>Contains Custom Lights</span> <dfn class='tooltip'>ⓘ<span>This object contains custom lighting effects. For more information, <a href='https://developer.x-plane.com/article/obj8-file-format-specification/#LIGHT_CUSTOM_ltxgt_ltygt_ltzgt_ltrgt_ltggt_ltbgt_ltagt_ltsgt_lts1gt_ltt1gt_lts2gt_ltt2gt_ltdatarefgt' target='_blank'>see the official documentation</a>.</span></dfn></li>\n";
        }

        if ($this->lightsNamed) {
            $result .= "<li><span class='fieldValue'>Contains Named Lights</span> <dfn class='tooltip'>ⓘ<span>This object contains named lighting effects. For more information, <a href='https://developer.x-plane.com/article/obj8-file-format-specification/#LIGHT_NAMED_ltnamegt_ltxgt_ltygt_ltzgt' target='_blank'>see the official documentation</a>.</span></dfn></li>\n";
        }

        if ($this->lightsParameterised) {
            $result .= "<li><span class='fieldValue'>Contains Parameterised Lights</span> <dfn class='tooltip'>ⓘ<span>This object contains parameterised lighting effects. For more information, <a href='https://developer.x-plane.com/article/obj8-file-format-specification/#LIGHT_PARAM_ltnamegt_ltxgt_ltygt_ltzgt_ltadditional_paramsgt' target='_blank'>see the official documentation</a>.</span></dfn></li>\n";
        }

        if ($this->lightsSpill) {
            $result .= "<li><span class='fieldValue'>Contains Spill Lights</span> <dfn class='tooltip'>ⓘ<span>This object contains spill lighting effects. For more information, <a href='https://developer.x-plane.com/article/obj8-file-format-specification/#LIGHT_SPILL_CUSTOM_ltxgt_ltygt_ltzgt_ltrgt_ltggt_ltbgt_ltagt_ltsgt_ltdxgt_ltdygt_ltdzgt_ltsemigt_ltdrefgt' target='_blank'>see the official documentation</a>.</span></dfn></li>\n";
        }

        if ($this->tilted) {
            $result .= "<li><span class='fieldValue'>Will Render Tilted</span> <dfn class='tooltip'>ⓘ<span>The base of this object will be aligned with the underlying terrain, which means it may be tilted if the terrain is sloping. For more information, <a href='https://developer.x-plane.com/article/obj8-file-format-specification/#TILTED' target='_blank'>see the official documentation</a>.</span></dfn></li>\n";
        }

        if ($this->smokeBlack) {
            $result .= "<li><span class='fieldValue'>Emits Black Smoke</span> <dfn class='tooltip'>ⓘ<span>This object emits black smoke. For more information, <a href='https://developer.x-plane.com/article/obj8-file-format-specification/#smoke_black_ltxgt_ltygt_ltzgt_ltsgt' target='_blank'>see the official documentation</a>.</span></dfn></li>\n";
        }

        if ($this->smokeWhite) {
            $result .= "<li><span class='fieldValue'>Emits White Smoke</span> <dfn class='tooltip'>ⓘ<span>This object emits white smoke. For more information, <a href='https://developer.x-plane.com/article/obj8-file-format-specification/#smoke_white_ltxgt_ltygt_ltzgt_ltsgt' target='_blank'>see the official documentation</a>.</span></dfn></li>\n";
        }

        if ($this->wedRotationLockAngle != null) {
            $result .= "<li><span class='fieldTitle'>Placement Locked at: </span> <span class='fieldValue'>" . $this->wedRotationLockAngle . "°</span> <span class='fieldTitle'>in WED</span> <dfn class='tooltip'>ⓘ<span>Scenery developers, when adding this object using <a href='https://developer.x-plane.com/tools/worldeditor/' target='_blank'>WED version 2.1 or higher</a>, the rotation will be locked at " . $this->wedRotationLockAngle . "°. This is because this object is designed to rotate in the wind, and to align with the correct wind direction in X-Plane® the placement angle must be locked at this value. If you are using <a href='https://marginal.org.uk/x-planescenery/tools.html' target='_blank'>OverlayEditor</a> or an older version of <a href='https://developer.x-plane.com/tools/worldeditor/' target='_blank'>WED</a> then please ensure you set the rotation to " . $this->wedRotationLockAngle . "°.</span></dfn></li>\n";
        }

        if ($this->samStaticAircraftDoors > 0) {
            $result .= "<li><span class='fieldTitle'>Has support for SAM animations, with </span><span class='fieldValue'>" . $this->samStaticAircraftDoors . "</span> <span class='fieldTitle'>door" . ($this->samStaticAircraftDoors > 1 ? "s" : "") . "</span> <dfn class='tooltip'>ⓘ<span>This object has " . $this->samStaticAircraftDoors . " door" . ($this->samStaticAircraftDoors > 1 ? "s" : "") . " that support" . ($this->samStaticAircraftDoors > 1 ? "" : "s") . " <a href='https://stairport-sceneries.com' target='_blank'>Scenery Animation Manager (SAM)</a> animated jetways.</span></dfn></li>\n";
        }

        if ($result != "") {
            $result = "<h2>Object-specific Details</h2><ul class='mainItemDetails'>\n" . $result . "</ul>\n";
        }

        return $result;
    }

    protected function getTypeExtension() {
        return "." . self::FILENAME_EXT;
    }
}
