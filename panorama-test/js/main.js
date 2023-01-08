const panoDiv = document.querySelector('.pano-image');
// const panoImage = '../mcpano3.png';

// const panorama = new PANOLENS.ImagePanorama(panoImage);

// cube panorama
const path = './mc-pano/';
const format = '.png';
panorama = new PANOLENS.CubePanorama( [
    path + '2_2' + format, path + '2_4' + format,
    path + '2_6' + format, path + '2_5' + format,
    path + '2_1' + format, path + '2_3' + format
] );
panorama2 = new PANOLENS.CubePanorama( [
    path + '3_2' + format, path + '3_4' + format,
    path + '3_6' + format, path + '3_5' + format,
    path + '3_1' + format, path + '3_3' + format
] );

// vector3: X i Z to ilość blocków odległości dzielone przez 2 i pomnożone przez 1000, w skrócie *500. Y nie wiem
panorama.link( panorama2, new THREE.Vector3( -6000.0, -1000.0, 2000.00 ) );
panorama2.link( panorama, new THREE.Vector3( 6000.00, 100.0, -2000.00 ) );

const viewer = new PANOLENS.Viewer({
    container: panoDiv,
    controlBar: false,
    autoHideInfospot: false
});

viewer.add(panorama, panorama2);
// viewer.getControl().rotateSpeed *= -1;