<script>
(function () {
    if (window.__cpalnyaCityCanvasBooted) {
        return;
    }
    window.__cpalnyaCityCanvasBooted = true;

    var canvas = document.getElementById('terrainFieldGlobal');
    if (!canvas) {
        return;
    }

    var ctx = canvas.getContext('2d', { alpha: true });
    if (!ctx) {
        return;
    }

    var dpr = Math.max(1, Math.min(2, window.devicePixelRatio || 1));
    var width = 0;
    var height = 0;
    var rafId = 0;
    var theme = 'enterprise';
    var scene = {
        avenues: [],
        crossStreets: [],
        blocks: [],
        pulses: [],
        horizon: 0,
        roadHalfWidth: 0
    };

    var pointer = {
        x: 0,
        y: 0,
        tx: 0,
        ty: 0,
        strength: 0,
        targetStrength: 0
    };

    function rand(min, max) {
        return min + Math.random() * (max - min);
    }

    function clamp(value, min, max) {
        return Math.min(max, Math.max(min, value));
    }

    function lerp(a, b, t) {
        return a + (b - a) * t;
    }

    function buildPalette(mode) {
        if (mode === 'simple') {
            return {
                skyTop: '#eef5ff',
                skyBottom: '#d6e5fb',
                hazeA: '105,138,255',
                hazeB: '0,174,163',
                groundA: '229,238,252',
                groundB: '210,223,245',
                gridMajor: '61,95,182',
                gridMinor: '93,135,207',
                roadGlowA: '86,119,255',
                roadGlowB: '0,176,167',
                buildingFront: '222,232,250',
                buildingSide: '207,220,244',
                roof: '240,246,255',
                edge: '101,135,198',
                windowA: '95,113,255',
                windowB: '37,169,154',
                park: '182,232,206',
                node: '48,111,221'
            };
        }

        return {
            skyTop: '#02060f',
            skyBottom: '#081425',
            hazeA: '111,95,255',
            hazeB: '29,228,192',
            groundA: '#060d19',
            groundB: '#08101e',
            gridMajor: '93,122,255',
            gridMinor: '28,168,158',
            roadGlowA: '149,98,255',
            roadGlowB: '38,233,198',
            buildingFront: '8,17,32',
            buildingSide: '12,25,46',
            roof: '14,31,58',
            edge: '119,174,255',
            windowA: '124,136,255',
            windowB: '43,232,196',
            park: '19,70,62',
            node: '171,209,255'
        };
    }

    function resize() {
        width = Math.max(window.innerWidth || 0, 320);
        height = Math.max(window.innerHeight || 0, 320);
        canvas.width = Math.round(width * dpr);
        canvas.height = Math.round(height * dpr);
        canvas.style.width = width + 'px';
        canvas.style.height = height + 'px';
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
        seedCity();
    }

    function project(x, z, y) {
        var horizon = scene.horizon;
        var perspective = clamp(1 - z / 1080, 0.045, 1.4);
        var swayX = (pointer.x - width * 0.5) / Math.max(width, 1) * 90 * perspective;
        var swayY = (pointer.y - height * 0.5) / Math.max(height, 1) * 32 * perspective;
        var deform = pointerDeform(x, z);
        return {
            x: width * 0.5 + x * perspective + swayX + deform.x,
            y: horizon + z * 0.78 - y * perspective * 0.92 + swayY + deform.y,
            scale: perspective
        };
    }

    function pointerDeform(x, z) {
        var screen = {
            x: width * 0.5 + x * clamp(1 - z / 1080, 0.045, 1.4),
            y: scene.horizon + z * 0.78
        };
        var dx = screen.x - pointer.x;
        var dy = screen.y - pointer.y;
        var dist = Math.sqrt(dx * dx + dy * dy) || 1;
        var reach = 220 + pointer.strength * 140;
        if (dist >= reach) {
            return { x: 0, y: 0 };
        }
        var falloff = 1 - dist / reach;
        var push = falloff * falloff * (28 + pointer.strength * 18);
        return {
            x: (dx / dist) * push,
            y: (dy / dist) * push * 0.7
        };
    }

    function seedCity() {
        scene.horizon = height * 0.23;
        scene.roadHalfWidth = Math.max(14, width * 0.012);
        scene.avenues = [];
        scene.crossStreets = [];
        scene.blocks = [];
        scene.pulses = [];

        var avenueCount = width < 900 ? 7 : 9;
        for (var i = 0; i < avenueCount; i++) {
            scene.avenues.push(lerp(-width * 0.58, width * 0.58, i / (avenueCount - 1)));
        }

        var depth = 0;
        while (depth < 980) {
            scene.crossStreets.push(depth);
            depth += rand(60, 120) + depth * 0.045;
        }

        for (var zIndex = 0; zIndex < scene.crossStreets.length - 1; zIndex++) {
            for (var xIndex = 0; xIndex < scene.avenues.length - 1; xIndex++) {
                var x0 = scene.avenues[xIndex];
                var x1 = scene.avenues[xIndex + 1];
                var z0 = scene.crossStreets[zIndex];
                var z1 = scene.crossStreets[zIndex + 1];
                var inset = rand(6, 16);
                var road = scene.roadHalfWidth;
                var left = x0 + road + inset;
                var right = x1 - road - inset;
                var nearZ = z0 + road + inset;
                var farZ = z1 - road - inset;
                if (right - left < 18 || farZ - nearZ < 24) {
                    continue;
                }
                scene.blocks.push(buildBlock(left, right, nearZ, farZ, zIndex));
            }
        }

        for (var p = 0; p < Math.max(14, scene.avenues.length * 2 + scene.crossStreets.length); p++) {
            if (Math.random() > 0.5) {
                scene.pulses.push({
                    axis: 'z',
                    lane: scene.avenues[Math.floor(rand(0, scene.avenues.length - 0.001))] + rand(-scene.roadHalfWidth * 0.35, scene.roadHalfWidth * 0.35),
                    progress: Math.random(),
                    speed: rand(0.00008, 0.0002),
                    span: rand(45, 85),
                    hue: Math.random()
                });
            } else {
                scene.pulses.push({
                    axis: 'x',
                    depth: scene.crossStreets[Math.floor(rand(1, scene.crossStreets.length - 1.001))],
                    progress: Math.random(),
                    speed: rand(0.00008, 0.00018),
                    span: rand(55, 105),
                    hue: Math.random()
                });
            }
        }
    }

    function buildBlock(left, right, nearZ, farZ, rowIndex) {
        var centerBias = 1 - Math.min(1, Math.abs((left + right) * 0.5) / (width * 0.34));
        var parkChance = rowIndex < 2 ? 0.22 : 0.1;
        var type = Math.random() < parkChance ? 'park' : 'buildings';
        var block = {
            left: left,
            right: right,
            nearZ: nearZ,
            farZ: farZ,
            type: type,
            buildings: []
        };

        if (type === 'buildings') {
            var widthSpan = right - left;
            var depthSpan = farZ - nearZ;
            var cols = Math.max(1, Math.floor(widthSpan / rand(48, 90)));
            var rows = Math.max(1, Math.floor(depthSpan / rand(54, 92)));
            var cellW = widthSpan / cols;
            var cellD = depthSpan / rows;

            for (var row = 0; row < rows; row++) {
                for (var col = 0; col < cols; col++) {
                    if (Math.random() < 0.16) {
                        continue;
                    }
                    var bw = cellW * rand(0.42, 0.84);
                    var bd = cellD * rand(0.42, 0.82);
                    var bx = left + col * cellW + (cellW - bw) * rand(0.08, 0.92);
                    var bz = nearZ + row * cellD + (cellD - bd) * rand(0.08, 0.92);
                    var heightUnits = rand(80, 260) + centerBias * rand(60, 220) + (1 - bz / 1000) * rand(30, 120);
                    block.buildings.push({
                        x: bx,
                        z: bz,
                        w: bw,
                        d: bd,
                        h: heightUnits
                    });
                }
            }

            block.buildings.sort(function (a, b) {
                return b.z - a.z;
            });
        }

        return block;
    }

    function drawBackdrop(palette) {
        var sky = ctx.createLinearGradient(0, 0, 0, height);
        sky.addColorStop(0, palette.skyTop);
        sky.addColorStop(0.54, palette.skyBottom);
        sky.addColorStop(1, palette.groundB);
        ctx.fillStyle = sky;
        ctx.fillRect(0, 0, width, height);

        var glowLeft = ctx.createRadialGradient(width * 0.24, scene.horizon * 0.76, 0, width * 0.24, scene.horizon * 0.76, width * 0.42);
        glowLeft.addColorStop(0, 'rgba(' + palette.hazeA + ',0.22)');
        glowLeft.addColorStop(1, 'rgba(' + palette.hazeA + ',0)');
        ctx.fillStyle = glowLeft;
        ctx.fillRect(0, 0, width, height);

        var glowRight = ctx.createRadialGradient(width * 0.72, scene.horizon * 0.9, 0, width * 0.72, scene.horizon * 0.9, width * 0.4);
        glowRight.addColorStop(0, 'rgba(' + palette.hazeB + ',0.18)');
        glowRight.addColorStop(1, 'rgba(' + palette.hazeB + ',0)');
        ctx.fillStyle = glowRight;
        ctx.fillRect(0, 0, width, height);

        var ground = ctx.createLinearGradient(0, scene.horizon, 0, height);
        ground.addColorStop(0, palette.groundA);
        ground.addColorStop(1, palette.groundB);
        ctx.fillStyle = ground;
        ctx.fillRect(0, scene.horizon, width, height - scene.horizon);
    }

    function drawRoadGrid(palette, time) {
        ctx.lineCap = 'round';

        for (var i = 0; i < scene.avenues.length; i++) {
            var avenue = scene.avenues[i];
            var top = project(avenue, 0, 0);
            var bottom = project(avenue, 1100, 0);

            ctx.strokeStyle = 'rgba(' + palette.gridMinor + ',0.18)';
            ctx.lineWidth = 1;
            ctx.beginPath();
            ctx.moveTo(top.x, top.y);
            ctx.lineTo(bottom.x, bottom.y);
            ctx.stroke();

            ctx.strokeStyle = 'rgba(' + palette.gridMajor + ',0.34)';
            ctx.lineWidth = 1.6;
            ctx.beginPath();
            ctx.moveTo(top.x + Math.sin(time * 0.00035 + i) * 0.8, top.y);
            ctx.lineTo(bottom.x, bottom.y);
            ctx.stroke();
        }

        for (var j = 0; j < scene.crossStreets.length; j++) {
            var z = scene.crossStreets[j];
            var left = project(scene.avenues[0], z, 0);
            var right = project(scene.avenues[scene.avenues.length - 1], z, 0);
            ctx.strokeStyle = 'rgba(' + palette.gridMinor + ',0.14)';
            ctx.lineWidth = 1;
            ctx.beginPath();
            ctx.moveTo(left.x, left.y);
            ctx.lineTo(right.x, right.y);
            ctx.stroke();
        }
    }

    function drawRoadSurfaces(palette) {
        for (var i = 0; i < scene.avenues.length - 1; i++) {
            var leftRoad = scene.avenues[i] + scene.roadHalfWidth;
            var rightRoad = scene.avenues[i + 1] - scene.roadHalfWidth;
            if (rightRoad <= leftRoad) {
                continue;
            }
            for (var j = 0; j < scene.crossStreets.length - 1; j++) {
                var nearZ = scene.crossStreets[j];
                var farZ = scene.crossStreets[j + 1];
                var a = project(leftRoad, nearZ, 0);
                var b = project(rightRoad, nearZ, 0);
                var c = project(rightRoad, farZ, 0);
                var d = project(leftRoad, farZ, 0);
                ctx.fillStyle = 'rgba(' + palette.gridMajor + ',' + clamp(0.035 + (1 - nearZ / 1100) * 0.06, 0.03, 0.08).toFixed(3) + ')';
                ctx.beginPath();
                ctx.moveTo(a.x, a.y);
                ctx.lineTo(b.x, b.y);
                ctx.lineTo(c.x, c.y);
                ctx.lineTo(d.x, d.y);
                ctx.closePath();
                ctx.fill();
            }
        }
    }

    function drawPark(block, palette, time) {
        var a = project(block.left, block.nearZ, 0);
        var b = project(block.right, block.nearZ, 0);
        var c = project(block.right, block.farZ, 0);
        var d = project(block.left, block.farZ, 0);
        ctx.fillStyle = 'rgba(' + palette.park + ',0.24)';
        ctx.strokeStyle = 'rgba(' + palette.gridMinor + ',0.3)';
        ctx.lineWidth = 1;
        ctx.beginPath();
        ctx.moveTo(a.x, a.y);
        ctx.lineTo(b.x, b.y);
        ctx.lineTo(c.x, c.y);
        ctx.lineTo(d.x, d.y);
        ctx.closePath();
        ctx.fill();
        ctx.stroke();

        for (var i = 0; i < 5; i++) {
            var tx = lerp(block.left, block.right, 0.18 + i * 0.15);
            var tz = lerp(block.nearZ, block.farZ, 0.25 + (i % 2) * 0.22);
            var tree = project(tx, tz, 16 + Math.sin(time * 0.0018 + i) * 5);
            ctx.fillStyle = 'rgba(' + palette.windowB + ',0.28)';
            ctx.beginPath();
            ctx.arc(tree.x, tree.y, 2 + tree.scale * 10, 0, Math.PI * 2);
            ctx.fill();
        }
    }

    function drawBuilding(building, palette, time) {
        var x0 = building.x;
        var x1 = building.x + building.w;
        var z0 = building.z;
        var z1 = building.z + building.d;
        var h = building.h + Math.sin(time * 0.0009 + building.x * 0.02 + building.z * 0.01) * 3;

        var fbl = project(x0, z0, 0);
        var fbr = project(x1, z0, 0);
        var bbr = project(x1, z1, 0);
        var bbl = project(x0, z1, 0);

        var tbl = project(x0, z0, h);
        var tbr = project(x1, z0, h);
        var tbbr = project(x1, z1, h);
        var tbbl = project(x0, z1, h);

        ctx.fillStyle = 'rgba(' + palette.buildingSide + ',0.72)';
        ctx.strokeStyle = 'rgba(' + palette.edge + ',0.2)';
        ctx.lineWidth = 1;
        ctx.beginPath();
        ctx.moveTo(fbr.x, fbr.y);
        ctx.lineTo(bbr.x, bbr.y);
        ctx.lineTo(tbbr.x, tbbr.y);
        ctx.lineTo(tbr.x, tbr.y);
        ctx.closePath();
        ctx.fill();
        ctx.stroke();

        ctx.fillStyle = 'rgba(' + palette.buildingFront + ',0.86)';
        ctx.beginPath();
        ctx.moveTo(fbl.x, fbl.y);
        ctx.lineTo(fbr.x, fbr.y);
        ctx.lineTo(tbr.x, tbr.y);
        ctx.lineTo(tbl.x, tbl.y);
        ctx.closePath();
        ctx.fill();
        ctx.stroke();

        ctx.fillStyle = 'rgba(' + palette.roof + ',0.62)';
        ctx.beginPath();
        ctx.moveTo(tbl.x, tbl.y);
        ctx.lineTo(tbr.x, tbr.y);
        ctx.lineTo(tbbr.x, tbbr.y);
        ctx.lineTo(tbbl.x, tbbl.y);
        ctx.closePath();
        ctx.fill();
        ctx.strokeStyle = 'rgba(' + palette.edge + ',0.38)';
        ctx.stroke();

        drawWindows(building, fbl, fbr, tbl, tbr, palette, time, true);
        drawWindows(building, fbr, bbr, tbr, tbbr, palette, time, false);
    }

    function drawWindows(building, bl, br, tl, tr, palette, time, isFront) {
        var rows = Math.max(2, Math.floor(building.h / 32));
        var cols = Math.max(2, Math.floor(building.w / (isFront ? 18 : 22)));
        for (var row = 0; row < rows; row++) {
            var v0 = (row + 0.22) / rows;
            var v1 = (row + 0.78) / rows;
            for (var col = 0; col < cols; col++) {
                if (Math.random() > 0.58) {
                    continue;
                }
                var u0 = (col + 0.18) / cols;
                var u1 = (col + 0.72) / cols;
                var p1 = quadPoint(bl, br, tl, tr, u0, v0);
                var p2 = quadPoint(bl, br, tl, tr, u1, v0);
                var p3 = quadPoint(bl, br, tl, tr, u1, v1);
                var p4 = quadPoint(bl, br, tl, tr, u0, v1);
                var alpha = clamp(0.12 + Math.sin(time * 0.0024 + building.x * 0.03 + row * 0.5 + col) * 0.12, 0.08, 0.36);
                ctx.fillStyle = 'rgba(' + ((row + col) % 2 ? palette.windowA : palette.windowB) + ',' + alpha.toFixed(3) + ')';
                ctx.beginPath();
                ctx.moveTo(p1.x, p1.y);
                ctx.lineTo(p2.x, p2.y);
                ctx.lineTo(p3.x, p3.y);
                ctx.lineTo(p4.x, p4.y);
                ctx.closePath();
                ctx.fill();
            }
        }
    }

    function quadPoint(bl, br, tl, tr, u, v) {
        var bottomX = lerp(bl.x, br.x, u);
        var bottomY = lerp(bl.y, br.y, u);
        var topX = lerp(tl.x, tr.x, u);
        var topY = lerp(tl.y, tr.y, u);
        return {
            x: lerp(bottomX, topX, v),
            y: lerp(bottomY, topY, v)
        };
    }

    function drawBlocks(palette, time) {
        for (var i = 0; i < scene.blocks.length; i++) {
            var block = scene.blocks[i];
            if (block.type === 'park') {
                drawPark(block, palette, time);
                continue;
            }
            for (var j = 0; j < block.buildings.length; j++) {
                drawBuilding(block.buildings[j], palette, time);
            }
        }
    }

    function drawPulses(palette, time) {
        ctx.lineCap = 'round';
        for (var i = 0; i < scene.pulses.length; i++) {
            var pulse = scene.pulses[i];
            pulse.progress += pulse.speed * 16;
            if (pulse.progress > 1) {
                pulse.progress = 0;
            }

            var color = pulse.hue > 0.5 ? palette.roadGlowA : palette.roadGlowB;
            if (pulse.axis === 'z') {
                var z = lerp(0, 1020, pulse.progress);
                var start = project(pulse.lane, Math.max(0, z - pulse.span), 0);
                var end = project(pulse.lane, Math.min(1040, z + pulse.span), 0);
                var gradZ = ctx.createLinearGradient(start.x, start.y, end.x, end.y);
                gradZ.addColorStop(0, 'rgba(' + color + ',0)');
                gradZ.addColorStop(0.5, 'rgba(' + color + ',0.82)');
                gradZ.addColorStop(1, 'rgba(' + color + ',0)');
                ctx.strokeStyle = gradZ;
                ctx.lineWidth = 2.8;
                ctx.beginPath();
                ctx.moveTo(start.x, start.y);
                ctx.lineTo(end.x, end.y);
                ctx.stroke();
            } else {
                var depth = pulse.depth;
                var spanX = lerp(width * 0.08, width * 0.32, 1 - depth / 1040);
                var x = lerp(-width * 0.5, width * 0.5, pulse.progress);
                var left = project(x - spanX, depth, 0);
                var right = project(x + spanX, depth, 0);
                var gradX = ctx.createLinearGradient(left.x, left.y, right.x, right.y);
                gradX.addColorStop(0, 'rgba(' + color + ',0)');
                gradX.addColorStop(0.5, 'rgba(' + color + ',0.76)');
                gradX.addColorStop(1, 'rgba(' + color + ',0)');
                ctx.strokeStyle = gradX;
                ctx.lineWidth = 2.4;
                ctx.beginPath();
                ctx.moveTo(left.x, left.y);
                ctx.lineTo(right.x, right.y);
                ctx.stroke();
            }
        }
    }

    function drawNodes(palette, time) {
        for (var i = 0; i < scene.avenues.length; i++) {
            for (var j = 1; j < scene.crossStreets.length; j += 2) {
                var point = project(scene.avenues[i], scene.crossStreets[j], 0);
                var glow = 1.5 + point.scale * 12 + Math.sin(time * 0.002 + i + j) * 0.8;
                ctx.fillStyle = 'rgba(' + palette.node + ',' + clamp(0.12 + point.scale * 0.28, 0.1, 0.34).toFixed(3) + ')';
                ctx.beginPath();
                ctx.arc(point.x, point.y, glow, 0, Math.PI * 2);
                ctx.fill();
            }
        }
    }

    function frame(ts) {
        var palette = buildPalette(theme);
        pointer.x = lerp(pointer.x, pointer.tx, 0.08);
        pointer.y = lerp(pointer.y, pointer.ty, 0.08);
        pointer.strength = lerp(pointer.strength, pointer.targetStrength, 0.08);

        ctx.clearRect(0, 0, width, height);
        drawBackdrop(palette);
        drawRoadSurfaces(palette);
        drawRoadGrid(palette, ts);
        drawBlocks(palette, ts);
        drawPulses(palette, ts);
        drawNodes(palette, ts);
        rafId = window.requestAnimationFrame(frame);
    }

    function onMove(event) {
        pointer.tx = event.clientX;
        pointer.ty = event.clientY;
        pointer.targetStrength = 1;
    }

    function onLeave() {
        pointer.tx = width * 0.5;
        pointer.ty = height * 0.56;
        pointer.targetStrength = 0;
    }

    window.__terrainApplyTheme = function (nextTheme) {
        theme = nextTheme === 'simple' ? 'simple' : 'enterprise';
    };

    window.addEventListener('resize', resize);
    window.addEventListener('mousemove', onMove, { passive: true });
    window.addEventListener('mouseleave', onLeave, { passive: true });
    window.addEventListener('blur', onLeave);

    resize();
    onLeave();
    window.cancelAnimationFrame(rafId);
    rafId = window.requestAnimationFrame(frame);
})();
</script>
