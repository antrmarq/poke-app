import "./bootstrap";
import React, { useEffect } from "react";
import ReactDOM from "react-dom/client";
import { Provider, useSelector } from "react-redux";
import { store } from "../store/index.js";
import MainBody from "./components/MainBody";

const ThemeHandler = () => {
    const theme = useSelector((state) => state.theme.mode);

    useEffect(() => {
        const htmlElement = document.getElementById("displaycolor");

        if (theme === "auto") {
            htmlElement.className = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
        } else {
            htmlElement.className = theme;
        }
    }, [theme]);

    return null;
};

ReactDOM.createRoot(document.getElementById("app")).render(
    <React.StrictMode>
        <Provider store={store}>
            <ThemeHandler />
            <MainBody />
        </Provider>
    </React.StrictMode>
);
