<?php

/**
 * Description of OSXLine
 */
class OSXLine extends OSXLibraryItem {
    const FILENAME_ROOT = "line";
    const FILENAME_EXT= "lin";

    protected $layerGroupName = null;
    protected $layerGroupOffset = null;
    protected $lineWidth = null;
    protected $mirror = null;

    function __construct($path, $url) {
        parent::__construct($path, $url, 'line');
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

                var skyLight = new THREE.HemisphereLight( 0xd1f3ff, 0xa0a0a0, 0.6 );
                var ambientLight = new THREE.AmbientLight( 0x404040 );
                var sunLight = new THREE.DirectionalLight(0xffffff, 0.4 );
                sunLight.position.set(-10000, -20000, -10000);

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
                var linLoader = new THREE.XPlaneLinLoader();
                linLoader.setPath(urlBase);

                linLoader.load(fileName, function (object) {
                    scene.add(object);
                    camera.position.set(0, 0.7, 0.7);
                });
            }
            </script>';

        wp_enqueue_script('3xplinloader', plugin_dir_url(__FILE__) . 'three.js/XPlaneLinLoader.js', array('three.js', '3ddsloader'), false, true);
        wp_add_inline_script('3xplinloader', $threejsScript, 'after');
    }

    protected function parse() {
        parent::parse();

        $matches = array();

        foreach ($this->fileLines as $line) {
            if (preg_match('/^Layer Group:\s+(.*)/', $line, $matches) === 1) {
                $this->layerGroupName = $matches[1];
                continue;
            }

            if (preg_match('/^Layer Offset:\s+(.*)/', $line, $matches) === 1) {
                $this->layerGroupOffset = $matches[1];
                continue;
            }

            if (preg_match('/^Line Width:\s+(.*)/', $line, $matches) === 1) {
                $this->lineWidth = $matches[1];
                continue;
            }

            if (preg_match('/^Mirror:\s+(.*)/', $line, $matches) === 1) {
                $this->mirror = ($matches[1] == "True" || $matches[1] == "Yes");
                continue;
            }
        }
    }

    protected function getTypeSpecificHTML() {
        $result = "";

        if ($this->layerGroupName !== null) {
            $result .= "<li><span class='fieldTitle'>Layer Group</span> <dfn class='tooltip'>ⓘ<span>This line is drawn as part of layer group <em>" . $this->layerGroupName . "</em>. For more information, <a href='https://developer.x-plane.com/article/painted-line-lin-file-format-specification/' target='_blank'>see the official documentation on the .lin format</a>, and the <a href='https://developer.x-plane.com/article/obj8-file-format-specification/#ATTR_layer_group_ltnamegt_ltoffsetgt' target='_blank'>official documentation on layer group names in the .obj format</a>.</span></dfn>: <span class='fieldValue'>" . $this->layerGroupName . "</span></li>\n";
        }

        if ($this->layerGroupOffset !== null) {
            $result .= "<li><span class='fieldTitle'>Layer Offset</span> <dfn class='tooltip'>ⓘ<span>This line is drawn at layer offset " . $this->layerGroupOffset . " within its layer group. -ve offsets are drawn earlier (underneath) and +ve are drawn later (on top). For more information, <a href='https://developer.x-plane.com/article/painted-line-lin-file-format-specification/' target='_blank'>see the official documentation on the .lin format</a>, and the <a href='https://developer.x-plane.com/article/obj8-file-format-specification/#ATTR_layer_group_ltnamegt_ltoffsetgt' target='_blank'>official documentation on layer group offsets in the .obj format</a>.</span></dfn>: <span class='fieldValue'>" . $this->layerGroupOffset . "</span></li>\n";
        }

        if ($this->lineWidth !== null) {
            $result .= "<li><span class='fieldTitle'>Line Width</span> <dfn class='tooltip'>ⓘ<span>This line has a width of " . self::dimension($this->lineWidth, self::UNITS_METRES) . " (" . self::dimension($this->lineWidth, self::UNITS_FEET) . "). Note that if this item includes multiple lines, this is the width of the widest.</span></dfn>: <span class='fieldValue'>" . self::dimension($this->lineWidth, self::UNITS_METRES) . " (" . self::dimension($this->lineWidth, self::UNITS_FEET) . ")</span></li>\n";
        }

        if ($this->mirror) {
            $result .= "<li><span class='fieldTitle'>Mirrored</span> <dfn class='tooltip'>ⓘ<span>X-Plane may reverse the texture direction of this line to form clean cuts at sharp corners. For more information, <a href='https://developer.x-plane.com/article/painted-line-lin-file-format-specification/' target='_blank'>see the official documentation on the .lin format</a>.</span></dfn></li>\n";
        }

        if ($result != "") {
            $result = "<h2>Line-specific Details</h2><ul>\n" . $result . "</ul>\n";
        }

        return $result;
    }

    protected function getTypeExtension() {
        return "." . self::FILENAME_EXT;
    }
}
