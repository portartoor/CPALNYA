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
    var timeSeed = Math.random() * 1000;
    var pointer = {
        x: 0,
        y: 0,
        tx: 0,
        ty: 0,
        strength: 0,
        targetStrength: 0
    };

    var city = {
        roadsX: [],
        roadsY: [],
        blocks: [],
        pulses: [],
        nodes: []
    };

    function rand(min, max) {
        return min + Math.random() * (max - min);
    }

    function clamp(value, min, max) {
        return Math.min(max, Math.max(min, value));
    }

    function mix(a, b, t) {
        return a + (b - a) * t;
    }

    function buildPalette(mode) {
        if (mode === 'simple') {
            return {
                bgA: '#eff6ff',
                bgB: '#d7e4f8',
                hazeA: '84,124,255',
                hazeB: '0,177,162',
                lineMajor: '42,89,165',
                lineMinor: '88,122,187',
                flowA: '68,110,255',
                flowB: '0,174,162',
                tower: '232,240,255',
                towerEdge: '107,135,194',
                windowA: '104,123,255',
                windowB: '47,179,162',
                park: '160,221,198',
                node: '45,106,221'
            };
        }

        return {
            bgA: '#030711',
            bgB: '#081325',
            hazeA: '104,114,255',
            hazeB: '38,224,194',
            lineMajor: '97,128,255',
            lineMinor: '24,150,150',
            flowA: '149,91,255',
            flowB: '47,232,194',
            tower: '10,19,37',
            towerEdge: '122,168,255',
            windowA: '117,146,255',
            windowB: '47,232,194',
            park: '24,76,67',
            node: '175,210,255'
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

    function buildRoads(length, minStep, maxStep, startPad, endPad) {
        var roads = [startPad];
        while (roads[roads.length - 1] < length - endPad) {
            roads.push(roads[roads.length - 1] + rand(minStep, maxStep));
        }
        roads[roads.length - 1] = length - endPad;
        return roads;
    }

    function pickBlockType(cx, cy) {
        var centerWeight = 1 - Math.min(1, Math.hypot(cx - width * 0.52, cy - height * 0.5) / Math.max(width, height));
        var parkChance = 0.1 + Math.max(0, 0.18 - centerWeight * 0.12);
        if (Math.random() < parkChance) {
            return 'park';
        }
        return 'towers';
    }

    function createBuildings(block) {
        var buildings = [];
        var cols = Math.max(1, Math.floor(block.w / rand(44, 66)));
        var rows = Math.max(1, Math.floor(block.h / rand(40, 58)));
        var cellW = block.w / cols;
        var cellH = block.h / rows;
        var centerBias = 1 - Math.min(1, Math.hypot(block.cx - width * 0.5, block.cy - height * 0.54) / (Math.max(width, height) * 0.58));

        for (var row = 0; row < rows; row++) {
            for (var col = 0; col < cols; col++) {
                if (Math.random() < 0.18) {
                    continue;
                }
                var w = cellW * rand(0.42, 0.82);
                var d = cellH * rand(0.4, 0.78);
                var x = block.x + col * cellW + (cellW - w) * rand(0.15, 0.85);
                var y = block.y + row * cellH + (cellH - d) * rand(0.15, 0.85);
                var heightUnits = rand(20, 140) + centerBias * rand(30, 110);
                buildings.push({
                    x: x,
                    y: y,
                    w: w,
                    d: d,
                    h: heightUnits
                });
            }
        }

        buildings.sort(function (a, b) {
            return (a.y + a.d) - (b.y + b.d);
        });
        return buildings;
    }

    function seedCity() {
        city.roadsX = buildRoads(width, 90, 170, 34, 34);
        city.roadsY = buildRoads(height, 82, 154, 42, 42);
        city.blocks = [];
        city.pulses = [];
        city.nodes = [];

        for (var yi = 0; yi < city.roadsY.length - 1; yi++) {
            for (var xi = 0; xi < city.roadsX.length - 1; xi++) {
                var x = city.roadsX[xi];
                var y = city.roadsY[yi];
                var w = city.roadsX[xi + 1] - x;
                var h = city.roadsY[yi + 1] - y;
                if (w < 46 || h < 42) {
                    continue;
                }
                var block = {
                    x: x + 8,
                    y: y + 8,
                    w: Math.max(18, w - 16),
                    h: Math.max(18, h - 16)
                };
                block.cx = block.x + block.w * 0.5;
                block.cy = block.y + block.h * 0.5;
                block.type = pickBlockType(block.cx, block.cy);
                block.buildings = block.type === 'park' ? [] : createBuildings(block);
                city.blocks.push(block);
            }
        }

        for (var rx = 0; rx < city.roadsX.length; rx++) {
            for (var ry = 0; ry < city.roadsY.length; ry++) {
                city.nodes.push({
                    x: city.roadsX[rx],
                    y: city.roadsY[ry],
                    phase: rand(0, Math.PI * 2),
                    power: rand(0.3, 1)
                });
            }
        }

        for (var i = 0; i < Math.max(10, Math.round((city.roadsX.length + city.roadsY.length) * 1.3)); i++) {
            var horizontal = Math.random() > 0.48;
            if (horizontal) {
                city.pulses.push({
                    axis: 'x',
                    y: city.roadsY[Math.floor(rand(0, city.roadsY.length - 1e-6))],
                    from: city.roadsX[0],
                    to: city.roadsX[city.roadsX.length - 1],
                    progress: Math.random(),
                    speed: rand(0.00005, 0.00013),
                    size: rand(60, 120),
                    hue: Math.random()
                });
            } else {
                city.pulses.push({
                    axis: 'y',
                    x: city.roadsX[Math.floor(rand(0, city.roadsX.length - 1e-6))],
                    from: city.roadsY[0],
                    to: city.roadsY[city.roadsY.length - 1],
                    progress: Math.random(),
                    speed: rand(0.00005, 0.00013),
                    size: rand(60, 120),
                    hue: Math.random()
                });
            }
        }
    }

    function drawBackdrop(palette) {
        var bg = ctx.createLinearGradient(0, 0, width, height);
        bg.addColorStop(0, palette.bgA);
        bg.addColorStop(1, palette.bgB);
        ctx.fillStyle = bg;
        ctx.fillRect(0, 0, width, height);

        var hazeA = ctx.createRadialGradient(width * 0.22, height * 0.2, 0, width * 0.22, height * 0.2, width * 0.48);
        hazeA.addColorStop(0, 'rgba(' + palette.hazeA + ',0.22)');
        hazeA.addColorStop(1, 'rgba(' + palette.hazeA + ',0)');
        ctx.fillStyle = hazeA;
        ctx.fillRect(0, 0, width, height);

        var hazeB = ctx.createRadialGradient(width * 0.78, height * 0.28, 0, width * 0.78, height * 0.28, width * 0.42);
        hazeB.addColorStop(0, 'rgba(' + palette.hazeB + ',0.18)');
        hazeB.addColorStop(1, 'rgba(' + palette.hazeB + ',0)');
        ctx.fillStyle = hazeB;
        ctx.fillRect(0, 0, width, height);
    }

    function pointerWarp(x, y) {
        var dx = x - pointer.x;
        var dy = y - pointer.y;
        var dist = Math.sqrt(dx * dx + dy * dy) || 1;
        var reach = 240 + pointer.strength * 120;
        if (dist > reach) {
            return { x: x, y: y };
        }
        var falloff = 1 - dist / reach;
        var push = falloff * falloff * (18 + pointer.strength * 32);
        return {
            x: x + (dx / dist) * push,
            y: y + (dy / dist) * push
        };
    }

    function drawRoads(palette, time) {
        var lineShift = Math.sin(time * 0.00018) * 0.5;
        ctx.lineCap = 'round';

        for (var i = 0; i < city.roadsX.length; i++) {
            var x = city.roadsX[i];
            var top = pointerWarp(x, city.roadsY[0]);
            var bottom = pointerWarp(x, city.roadsY[city.roadsY.length - 1]);

            ctx.strokeStyle = 'rgba(' + palette.lineMinor + ',0.16)';
            ctx.lineWidth = 1;
            ctx.beginPath();
            ctx.moveTo(top.x + lineShift, top.y);
            ctx.lineTo(bottom.x + lineShift, bottom.y);
            ctx.stroke();

            ctx.strokeStyle = 'rgba(' + palette.lineMajor + ',0.34)';
            ctx.lineWidth = 1.5;
            ctx.beginPath();
            ctx.moveTo(top.x, top.y);
            ctx.lineTo(bottom.x, bottom.y);
            ctx.stroke();
        }

        for (var j = 0; j < city.roadsY.length; j++) {
            var y = city.roadsY[j];
            var left = pointerWarp(city.roadsX[0], y);
            var right = pointerWarp(city.roadsX[city.roadsX.length - 1], y);

            ctx.strokeStyle = 'rgba(' + palette.lineMinor + ',0.16)';
            ctx.lineWidth = 1;
            ctx.beginPath();
            ctx.moveTo(left.x, left.y + lineShift);
            ctx.lineTo(right.x, right.y + lineShift);
            ctx.stroke();

            ctx.strokeStyle = 'rgba(' + palette.lineMajor + ',0.34)';
            ctx.lineWidth = 1.5;
            ctx.beginPath();
            ctx.moveTo(left.x, left.y);
            ctx.lineTo(right.x, right.y);
            ctx.stroke();
        }
    }

    function drawPark(block, palette, time) {
        var p = pointerWarp(block.cx, block.cy);
        var offsetX = (p.x - block.cx) * 0.25;
        var offsetY = (p.y - block.cy) * 0.25;
        var radius = Math.min(block.w, block.h) * 0.2;

        ctx.fillStyle = 'rgba(' + palette.park + ',0.22)';
        ctx.strokeStyle = 'rgba(' + palette.lineMinor + ',0.26)';
        ctx.lineWidth = 1;
        ctx.beginPath();
        ctx.roundRect(block.x + offsetX, block.y + offsetY, block.w, block.h, radius);
        ctx.fill();
        ctx.stroke();

        for (var i = 0; i < 4; i++) {
            var treeX = block.x + block.w * rand(0.2, 0.8) + offsetX;
            var treeY = block.y + block.h * rand(0.24, 0.76) + offsetY;
            var glow = 6 + Math.sin(time * 0.002 + i) * 2;
            ctx.fillStyle = 'rgba(' + palette.windowB + ',0.28)';
            ctx.beginPath();
            ctx.arc(treeX, treeY, glow, 0, Math.PI * 2);
            ctx.fill();
        }
    }

    function drawBuildings(palette, time) {
        for (var i = 0; i < city.blocks.length; i++) {
            var block = city.blocks[i];
            if (block.type === 'park') {
                drawPark(block, palette, time);
                continue;
            }

            for (var j = 0; j < block.buildings.length; j++) {
                var building = block.buildings[j];
                var center = pointerWarp(building.x + building.w * 0.5, building.y + building.d * 0.5);
                var nudgeX = (center.x - (building.x + building.w * 0.5)) * 0.2;
                var nudgeY = (center.y - (building.y + building.d * 0.5)) * 0.2;
                var roofLift = building.h * 0.18;
                var wobble = Math.sin(time * 0.0012 + building.x * 0.02 + building.y * 0.03) * 2;
                var bx = building.x + nudgeX;
                var by = building.y + nudgeY;
                var topY = by - roofLift - wobble;

                ctx.fillStyle = 'rgba(' + palette.tower + ',0.74)';
                ctx.strokeStyle = 'rgba(' + palette.towerEdge + ',0.22)';
                ctx.lineWidth = 1;
                ctx.beginPath();
                ctx.moveTo(bx, by + building.d);
                ctx.lineTo(bx, by);
                ctx.lineTo(bx + building.w, by);
                ctx.lineTo(bx + building.w, by + building.d);
                ctx.closePath();
                ctx.fill();
                ctx.stroke();

                ctx.fillStyle = 'rgba(' + palette.tower + ',0.52)';
                ctx.beginPath();
                ctx.moveTo(bx, by);
                ctx.lineTo(bx + building.w * 0.24, topY);
                ctx.lineTo(bx + building.w * 1.24, topY);
                ctx.lineTo(bx + building.w, by);
                ctx.closePath();
                ctx.fill();

                ctx.strokeStyle = 'rgba(' + palette.towerEdge + ',0.46)';
                ctx.beginPath();
                ctx.moveTo(bx, by);
                ctx.lineTo(bx + building.w * 0.24, topY);
                ctx.lineTo(bx + building.w * 1.24, topY);
                ctx.lineTo(bx + building.w, by);
                ctx.stroke();

                var rows = Math.max(2, Math.floor((roofLift + building.d) / 18));
                var cols = Math.max(2, Math.floor(building.w / 14));
                for (var row = 0; row < rows; row++) {
                    for (var col = 0; col < cols; col++) {
                        if (Math.random() > 0.5) {
                            continue;
                        }
                        var wx = bx + 5 + col * ((building.w - 10) / cols);
                        var wy = by + 4 + row * ((building.d - 8) / rows);
                        var alpha = 0.12 + Math.sin(time * 0.003 + wx * 0.05 + wy * 0.06) * 0.08;
                        ctx.fillStyle = 'rgba(' + (col % 2 ? palette.windowA : palette.windowB) + ',' + clamp(alpha, 0.08, 0.34).toFixed(3) + ')';
                        ctx.fillRect(wx, wy, 3, 6);
                    }
                }
            }
        }
    }

    function drawFlows(palette, time) {
        ctx.lineCap = 'round';
        for (var i = 0; i < city.pulses.length; i++) {
            var pulse = city.pulses[i];
            pulse.progress += pulse.speed * 16;
            if (pulse.progress > 1) {
                pulse.progress = 0;
            }

            var color = pulse.hue > 0.5 ? palette.flowA : palette.flowB;
            var position = mix(pulse.from, pulse.to, pulse.progress);
            if (pulse.axis === 'x') {
                var start = pointerWarp(position - pulse.size, pulse.y);
                var end = pointerWarp(position + pulse.size, pulse.y);
                var gradX = ctx.createLinearGradient(start.x, start.y, end.x, end.y);
                gradX.addColorStop(0, 'rgba(' + color + ',0)');
                gradX.addColorStop(0.5, 'rgba(' + color + ',0.72)');
                gradX.addColorStop(1, 'rgba(' + color + ',0)');
                ctx.strokeStyle = gradX;
                ctx.lineWidth = 2.4;
                ctx.beginPath();
                ctx.moveTo(start.x, start.y);
                ctx.lineTo(end.x, end.y);
                ctx.stroke();
            } else {
                var startY = pointerWarp(pulse.x, position - pulse.size);
                var endY = pointerWarp(pulse.x, position + pulse.size);
                var gradY = ctx.createLinearGradient(startY.x, startY.y, endY.x, endY.y);
                gradY.addColorStop(0, 'rgba(' + color + ',0)');
                gradY.addColorStop(0.5, 'rgba(' + color + ',0.72)');
                gradY.addColorStop(1, 'rgba(' + color + ',0)');
                ctx.strokeStyle = gradY;
                ctx.lineWidth = 2.4;
                ctx.beginPath();
                ctx.moveTo(startY.x, startY.y);
                ctx.lineTo(endY.x, endY.y);
                ctx.stroke();
            }
        }
    }

    function drawNodes(palette, time) {
        for (var i = 0; i < city.nodes.length; i++) {
            var node = city.nodes[i];
            var p = pointerWarp(node.x, node.y);
            var pulse = 1.6 + Math.sin(time * 0.0024 + node.phase) * node.power * 1.8;
            ctx.fillStyle = 'rgba(' + palette.node + ',' + (0.22 + node.power * 0.24).toFixed(3) + ')';
            ctx.beginPath();
            ctx.arc(p.x, p.y, pulse + 1.2, 0, Math.PI * 2);
            ctx.fill();
        }
    }

    function frame(ts) {
        var palette = buildPalette(theme);
        timeSeed += 0.35;
        pointer.x = mix(pointer.x, pointer.tx, 0.08);
        pointer.y = mix(pointer.y, pointer.ty, 0.08);
        pointer.strength = mix(pointer.strength, pointer.targetStrength, 0.08);

        ctx.clearRect(0, 0, width, height);
        drawBackdrop(palette);
        drawRoads(palette, ts + timeSeed);
        drawBuildings(palette, ts + timeSeed);
        drawFlows(palette, ts + timeSeed);
        drawNodes(palette, ts + timeSeed);
        rafId = window.requestAnimationFrame(frame);
    }

    function onMove(event) {
        pointer.tx = event.clientX;
        pointer.ty = event.clientY;
        pointer.targetStrength = 1;
    }

    function onLeave() {
        pointer.tx = width * 0.5;
        pointer.ty = height * 0.5;
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
