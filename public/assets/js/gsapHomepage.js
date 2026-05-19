import gsap from "gsap";
import ScrollTrigger from "gsap/ScrollTrigger";
import Draggable from "gsap/Draggable";

gsap.registerPlugin(ScrollTrigger, Draggable);

document.addEventListener("DOMContentLoaded", () => {
    let benefits = document.querySelector("#benefits");
    let benefitsWrapper = document.querySelector(".benefits");
    let container = document.querySelector(".container-medium");

    let containerPadding = (window.innerWidth - container.clientWidth) / 2;
    let totalWidth = benefitsWrapper.scrollWidth - benefits.clientWidth + containerPadding;

    let scrollTween = gsap.to(benefitsWrapper, {
        x: -totalWidth,
        ease: "none",
        scrollTrigger: {
            trigger: benefits,
            start: "top bottom",
            end: "bottom top",
            scrub: 1
        }
    });

    Draggable.create(benefitsWrapper, {
        type: "x",
        bounds: { minX: -totalWidth, maxX: 0 },
        inertia: true,
        edgeResistance: 1.9,
        cursor: "grab",
        onPress() {
            gsap.to(benefitsWrapper, { cursor: "grabbing" });
        },
        onRelease() {
            gsap.to(benefitsWrapper, { cursor: "grab" });
        },
        onDrag() {
            scrollTween.progress(-this.x / totalWidth);
        }
    });
});
