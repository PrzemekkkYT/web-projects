const panoDiv = document.querySelector('.pano-image');
const mapSrc = "json/mc_server.json";

const viewer = new PANOLENS.Viewer({
    container: panoDiv,
    controlBar: true,
    autoHideInfospot: false,
    cameraFov: 90
});

const startApp = async () => {
    const map = await fetchData();
    // console.log(map);
    // console.log(map["panoramas"][0])
    if (dataIntegrity(map) == 0) {
        const panoramas = createPanos(map);
        console.log(panoramas);
        panosConnections(map, panoramas);
        initializePanos(map, panoramas);
    } else {
        console.error("map.json has some integrity issues, fix them to make it work properly!")
    }
}

const fetchData = () => {
    return fetch(mapSrc).then((response) => response.json());
}

const dataIntegrity = (map) => {
    let errorCount = 0;
    if (!map.panoramas) {
        console.warn(`'panoramas' object is missing in map.json!!!`);
        errorCount += 1;
    }

    return errorCount
}

const panoIntegrity = (id, pano_settings, map) => {
    let errorCount = 0;
    switch (pano_settings.panoType) {
        case "image":
            if (!pano_settings.image) {
                console.warn(`'image' is missing in '${id}'`);
                errorCount += 1;
            }
            break;
        case "cubemap":
            if (!pano_settings.src) {
                console.warn(`'src' is missing in '${id}'`);
                errorCount += 1;
            }
            if (!pano_settings.images) {
                console.warn(`'images' is missing in '${id}'`);
                errorCount += 1;
            }
            if (Object.keys(pano_settings.images).length != 6) {
                console.warn(`Some panorama images are missing in '${id}', there are only ${Object.keys(pano_settings.images).length} sides declared instead of 6`);
                errorCount += 1;
            }
            break;
        default:
            console.warn(`no panoType selected in ${id}`)
            break;
    }
    switch (map.settings.coordinatesType) {
        default:
        case "relative":
            for (let conn in pano_settings.connections) {
                if (conn.position.length != 3) {
                    console.warn(`Some coordinates are missing in ${id}/${conn.id}, there are only ${conn.position.length} values instead of 3`);
                    errorCount += 1;
                }
            }
            break;
        case "absolute":
        case "geographic":
            if (pano_settings.position.length != 3) {
                console.warn(`Some coordinates are missing in ${id}, there are only ${pano_settings.position.length} values instead of 3`);
                errorCount += 1;
            }
            break;
    }
    return errorCount;
}


const createPanos = (map) => {
    let panoramas = {};

    for (let id in map.panoramas) {
        let pano_settings = map.panoramas[id];
        if (panoIntegrity(id, pano_settings, map) == 0) {
            switch (pano_settings.panoType) {
                case "cubemap":
                    path = pano_settings.src;
                    try {
                        let newPano = new PANOLENS.CubePanorama( [
                            path + pano_settings.images.right, path + pano_settings.images.left,
                            path + pano_settings.images.top, path + pano_settings.images.bottom,
                            path + pano_settings.images.front, path + pano_settings.images.back
                        ] );
                        newPano.uuid = id;
                        panoramas[id] = newPano;
                    } catch (error) {
                        console.error(error);
                    }
                    break;
                case "image":
                    path = pano_settings.image;
                    try {
                        let newPano = new PANOLENS.ImagePanorama(path);
                        panoramas[id] = newPano;
                    } catch (error) {
                        console.error(error);
                    }
                    break;
                default:
                    console.warn(`no panoType selected in ${id}`)
                    break;
            }
        } else {
            console.error(`${id} has some integrity issues, fix them to make it work properly!`)
        }
    }


    return panoramas
}

const panosConnections = (map, panoramas) => {
    switch (map.settings.coordinatesType) {
        default:
        case "relative":
            for (let id in panoramas) {
                if (Object.keys(map.panoramas[id].connections).length > 0) {
                    map.panoramas[id].connections.forEach(conn => {
                        if (conn.id in panoramas) {
                            panoramas[id].link(panoramas[conn.id], new THREE.Vector3( conn.position[0]*500, conn.position[1]*500, conn.position[2]*500 ));
                        }
                    });
                }
            }
            break;
        case "absolute":
            for (let id in panoramas) {
                if (Object.keys(map.panoramas[id].connections).length > 0) {
                    map.panoramas[id].connections.forEach(conn => {
                        if (conn in panoramas) {
                            if (map.settings.cameraAltitude) {
                                if (map.panoramas[conn].position[1] == map.panoramas[id].position[1]) {
                                    panoramas[id].link(panoramas[conn], new THREE.Vector3(
                                        (map.panoramas[conn].position[0]-map.panoramas[id].position[0])*500,
                                        -(map.settings.cameraAltitude)*500,
                                        (map.panoramas[conn].position[2]-map.panoramas[id].position[2])*500
                                    ));
                                } else {
                                    panoramas[id].link(panoramas[conn], new THREE.Vector3(
                                        (map.panoramas[conn].position[0]-map.panoramas[id].position[0])*500,
                                        (map.panoramas[conn].position[1]-map.panoramas[id].position[1]-map.settings.cameraAltitude)*500,
                                        (map.panoramas[conn].position[2]-map.panoramas[id].position[2])*500
                                    ));
                                }
                            } else {
                                panoramas[id].link(panoramas[conn], new THREE.Vector3(
                                    (map.panoramas[conn].position[0]-map.panoramas[id].position[0])*500,
                                    -(map.panoramas[id].position[1]-map.panoramas[conn].position[1])*500,
                                    (map.panoramas[conn].position[2]-map.panoramas[id].position[2])*500
                                ));
                            }
                        }
                    });
                }
            }
    }
    for (let id in panoramas) {
        for (let spot_num in panoramas[id].linkedSpots) {
            let spot = panoramas[id].linkedSpots[spot_num];
            spot.addHoverText(spot.toPanorama.uuid.replace("_"," "));
            spot.addEventListener("click", () => {
                document.querySelectorAll(".panolens-infospot").forEach(panolens_infospot => {
                    if (panolens_infospot.style.display != "none") {
                        panolens_infospot.style.display = 'none';
                    }
                });
            });
        }
    }
    
    // console.info(panoramas);
}
// kontynuować robienie automatycznych panoram z ustalonej mapy.

const initializePanos = (map, panoramas) => {
    if (map.settings.starterPoint in panoramas) {
        viewer.add(panoramas[map.settings.starterPoint])
    }
    for (let id in panoramas) {
        if (id != map.settings.starterPoint) {
            viewer.add(panoramas[id])
        }
    }
}

// viewer.add(panorama, panorama2);
// viewer.getControl().rotateSpeed *= -1;
startApp()