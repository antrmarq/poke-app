import React from "react";
import { useSelector, useDispatch } from "react-redux";
import { setTheme } from "../../../store/themeSlice.js";

const Footer = () => {
    const theme = useSelector((state) => state.theme.mode);
    const dispatch = useDispatch();

    const toggleTheme = () => {
        const newTheme = theme === "light" ? "dark" : theme === "dark" ? "auto" : "light";
        dispatch(setTheme(newTheme));
    };

    return (
        <footer className="bg-gray-800 text-white text-center p-4">
            <p>&copy; 2021 - Laravel React SPA</p>
            <button
                onClick={toggleTheme}
                className="ml-4 px-4 py-2 bg-blue-500 text-white rounded"
            >
                Toggle Theme (Current: {theme})
            </button>
        </footer>
    );
};

export default Footer;
