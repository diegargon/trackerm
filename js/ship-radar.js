
var container,
    container2,
    camera,
    scene,
    renderer,
    axes,
    camera2,
    scene2,
    renderer2,
    axes2,
    cube,
    sphere,
    triangle,
    CANVAS_WIDTH = 100,
    CANVAS_HEIGHT = 100,
    CAM_DISTANCE = 200;

// main canvas
// -----------------------------------------------

// dom
container = document.getElementById( 'canvas' );

// renderer
renderer = new THREE.WebGLRenderer();
renderer.setClearColor( 0x000000, 1 );
var w = container.offsetWidth;
var h = container.offsetHeight;
renderer.setSize( w, h );
container.appendChild( renderer.domElement );

// scene
scene = new THREE.Scene();

// camera
camera = new THREE.PerspectiveCamera( 50, w / h, 1, 200000 );
camera.position.y = 150;
camera.position.z = 500;

// controlls
controls = new THREE.TrackballControls( camera, renderer.domElement );

// cube
cube = new THREE.Mesh( 
    new THREE.BoxGeometry( 1000, 1000, 1000, 1, 1, 1 ), 
    new THREE.MeshBasicMaterial( { color : 0x0000ff,  wireframe: true } 
) );
// Sphere
sphere = new THREE.Mesh( 
    new THREE.SphereGeometry( 200, 15, 6 ), 
    new THREE.MeshBasicMaterial( { color : 0xff0000, wireframe: true } 
) );

sphere.position.x = 1500;
sphere.position.y = 1500;
sphere.position.z = 1500;

// Triangle
//const triangle_lined = new THREE.LineSegments( edges, new THREE.LineBasicMaterial( { color: 0xffffff } ) );

var triangle_geometry = new THREE.ConeGeometry( 2, 5, 5 );
var triangle_material = new  THREE.MeshBasicMaterial(  {color: 0xffff00 });
var triangle_material_outline = new  THREE.MeshBasicMaterial(  { color: 0xff0000, side: THREE.BackSide, wireframe: true } );
triangle = new THREE.Mesh(triangle_geometry, triangle_material);
triangle_outline = new THREE.Mesh(triangle_geometry, triangle_material_outline);
triangle.rotation.x = Math.PI / 2;
triangle_outline.rotation.x = Math.PI / 2;

// ADD
scene.add( cube );
scene.add( sphere );
scene.add( triangle);
scene.add( triangle_outline);
// axes
axes = new THREE.AxisHelper( 100 );
scene.add( axes );

// inset canvas
// -----------------------------------------------
// dom
container2 = document.getElementById('canvas_inset');

// renderer

renderer2 = new THREE.WebGLRenderer( { alpha: true } );
renderer2.setClearColor( 0xffffff, 0 );
renderer2.setSize( CANVAS_WIDTH, CANVAS_HEIGHT );
container2.appendChild( renderer2.domElement );

// scene
scene2 = new THREE.Scene();
scene2.background = null;
// camera
camera2 = new THREE.PerspectiveCamera( 50, CANVAS_WIDTH / CANVAS_HEIGHT, 1, 1000 );
camera2.up = camera.up; // important!

// axes
axes2 = new THREE.AxisHelper( 100 );
scene2.add( axes2 );

// animate
// -----------------------------------------------

function render() {

    renderer.render( scene, camera );
    renderer2.render( scene2, camera2 );

}

(function animate() {
    requestAnimationFrame( animate );
    controls.update();
    
    camera2.position.copy( camera.position );
    camera2.position.sub( controls.target );
    camera2.position.setLength( CAM_DISTANCE );
    camera2.lookAt( scene2.position );
    render();
})();
