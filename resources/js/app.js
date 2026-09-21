import "./bootstrap";
import "./echo";
import "./components/toast";
// import Alpine from "alpinejs"; // Removed to avoid conflict with Livewire's Alpine
import intersect from "@alpinejs/intersect";
import ApexCharts from "apexcharts";
import flatpickr from "flatpickr";
import "flatpickr/dist/flatpickr.min.css";
import { Calendar } from "@fullcalendar/core";
import Swiper from "swiper";
import { Navigation, Pagination } from "swiper/modules";
import "swiper/css";
import "swiper/css/navigation";
import "swiper/css/pagination";

// Stores

import { initSidebarStore } from "./components/sidebar";

// 1. Initialize & Globalize Alpine
// 1. Initialize & Globalize Alpine
// We use the alpine:init event to register stores on the Alpine instance that Livewire uses.
// This prevents "two Alpines" race conditions.

document.addEventListener("alpine:init", () => {
    // Register Plugins
    window.Alpine.plugin(intersect);

    // Register Stores

    initSidebarStore();

    // Media Slider Component
    window.Alpine.data("mediaSlider", () => ({
        swiper: null,
        init() {
            this.swiper = new Swiper(this.$refs.container, {
                modules: [Navigation, Pagination],
                slidesPerView: 1,
                spaceBetween: 0,
                pagination: {
                    el: this.$refs.pagination,
                    clickable: true,
                    dynamicBullets: true,
                },
                navigation: {
                    nextEl: this.$refs.next,
                    prevEl: this.$refs.prev,
                },
            });
        },
    }));

    // Activity Map Component
    window.Alpine.data("activityMap", (mapData) => ({
        loaded: false,
        map: null,
        polyline: null,

        initMap() {
            if (!this.$refs.mapContainer || this.map) return;

            // Initialization wait for container to be ready
            // especially during Livewire updates
            this.map = L.map(this.$refs.mapContainer, {
                zoomControl: false,
                dragging: false,
                touchZoom: false,
                scrollWheelZoom: false,
                doubleClickZoom: false,
                boxZoom: false,
                tap: false,
            });

            // Carto Tiles - Premium look & supports Dark Mode
            const isDark = document.documentElement.classList.contains("dark");
            const tileLayer = isDark
                ? "https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png"
                : "https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png";

            L.tileLayer(tileLayer, {
                attribution: "© OSM",
            }).addTo(this.map);

            // Invalidation of size to ensure it renders correctly after container readiness
            setTimeout(() => {
                if (this.map) {
                    this.map.invalidateSize();
                }
            }, 500);

            // Remove o logo do Leaflet via DOM (mantém apenas "© OSM" obrigatório)
            this.$nextTick(() => {
                const attr = this.$refs.mapContainer.querySelector(
                    ".leaflet-control-attribution",
                );
                if (attr) {
                    // Remove o link "Leaflet" mas mantém o texto OSM
                    attr.querySelectorAll('a[href*="leaflet"]').forEach((el) =>
                        el.remove(),
                    );
                    attr.style.cssText =
                        "font-size:9px;background:rgba(255,255,255,0.65);padding:1px 4px;border-radius:3px;";
                }
            });

            let points = [];
            if (mapData.type === "encoded") {
                points = this.decodePolyline(mapData.data);
                this.renderPoints(points);
                this.loaded = true;
            } else if (mapData.type === "points") {
                points = mapData.data.map((p) => [p.lat, p.lng]);
                this.renderPoints(points);
                this.loaded = true;
            } else if (mapData.type === "geocode" && mapData.data) {
                // Free geocoding via Nominatim (OpenStreetMap)
                const q = encodeURIComponent(mapData.data);
                fetch(
                    `https://nominatim.openstreetmap.org/search?format=json&q=${q}&limit=1`,
                    {
                        headers: { "Accept-Language": "pt-BR" },
                    },
                )
                    .then((r) => r.json())
                    .then((results) => {
                        if (results && results.length > 0) {
                            const lat = parseFloat(results[0].lat);
                            const lon = parseFloat(results[0].lon);
                            this.map.setView([lat, lon], 13);
                            L.circleMarker([lat, lon], {
                                radius: 8,
                                fillColor: "#22c55e",
                                color: "#fff",
                                weight: 2,
                                opacity: 1,
                                fillOpacity: 0.9,
                            }).addTo(this.map);
                        } else {
                            // Default to Brazil center
                            this.map.setView([-14.235, -51.9253], 4);
                        }
                        this.loaded = true;
                    })
                    .catch(() => {
                        this.map.setView([-14.235, -51.9253], 4);
                        this.loaded = true;
                    });
            } else {
                this.loaded = true;
            }
        },

        renderPoints(points) {
            if (points.length > 1) {
                this.polyline = L.polyline(points, {
                    color: "#22c55e", // verde (Tailwind green-500)
                    weight: 5,
                    opacity: 0.95,
                    smoothFactor: 1.5,
                    lineCap: "round",
                    lineJoin: "round",
                }).addTo(this.map);
                this.map.fitBounds(this.polyline.getBounds(), {
                    padding: [20, 20],
                });
            } else if (points.length === 1) {
                this.map.setView(points[0], 15);
                L.circleMarker(points[0], {
                    radius: 9,
                    fillColor: "#22c55e",
                    color: "#fff",
                    weight: 2.5,
                    opacity: 1,
                    fillOpacity: 0.9,
                }).addTo(this.map);
            }
        },

        decodePolyline(str, precision) {
            var index = 0,
                lat = 0,
                lng = 0,
                coordinates = [],
                shift = 0,
                result = 0,
                byte = null,
                latitude_change,
                longitude_change,
                factor = Math.pow(10, precision || 5);
            while (index < str.length) {
                shift = 0;
                result = 0;
                do {
                    byte = str.charCodeAt(index++) - 63;
                    result |= (byte & 0x1f) << shift;
                    shift += 5;
                } while (byte >= 0x20);
                latitude_change = result & 1 ? ~(result >> 1) : result >> 1;
                lat += latitude_change;
                shift = 0;
                result = 0;
                do {
                    byte = str.charCodeAt(index++) - 63;
                    result |= (byte & 0x1f) << shift;
                    shift += 5;
                } while (byte >= 0x20);
                longitude_change = result & 1 ? ~(result >> 1) : result >> 1;
                lng += longitude_change;
                coordinates.push([lat / factor, lng / factor]);
            }
            return coordinates;
        },
    }));

    console.log("Alpine stores and plugins initialized for Livewire 3");
});

// 4. Global Libraries
window.ApexCharts = ApexCharts;
window.flatpickr = flatpickr;
window.FullCalendar = Calendar;

// 5. Localization & Components
const Portuguese = {
    weekdays: {
        shorthand: ["Dom", "Seg", "Ter", "Qua", "Qui", "Sex", "Sáb"],
        longhand: [
            "Domingo",
            "Segunda-feira",
            "Terça-feira",
            "Quarta-feira",
            "Quinta-feira",
            "Sexta-feira",
            "Sábado",
        ],
    },
    months: {
        shorthand: [
            "Jan",
            "Fev",
            "Mar",
            "Abr",
            "Mai",
            "Jun",
            "Jul",
            "Ago",
            "Set",
            "Out",
            "Nov",
            "Dez",
        ],
        longhand: [
            "Janeiro",
            "Fevereiro",
            "Março",
            "Abril",
            "Maio",
            "Junho",
            "Julho",
            "Agosto",
            "Setembro",
            "Outubro",
            "Novembro",
            "Dezembro",
        ],
    },
    rangeSeparator: " até ",
    time_24hr: true,
};
flatpickr.localize(Portuguese);

document.addEventListener("DOMContentLoaded", () => {
    if (document.querySelector("#goalChart")) {
        import("./components/goals-chart").then((m) => m.initGoalChart());
    }
    if (document.querySelector("#userGrowthChart")) {
        import("./components/user-growth-chart");
    }
    if (document.querySelector("#calendar")) {
        import("./components/calendar-init").then((m) => m.calendarInit());
    }
    import("./components/quill-editor").then((m) => m.initQuillEditor());
});
