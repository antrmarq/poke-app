import { createSlice } from "@reduxjs/toolkit";

// Load theme from local storage or default to "auto"
const getInitialTheme = () => {
  return localStorage.getItem("theme") || "auto";
};

const themeSlice = createSlice({
  name: "theme",
  initialState: {
    mode: getInitialTheme(), // Set initial state from local storage
  },
  reducers: {
    setTheme: (state, action) => {
      state.mode = action.payload;
      localStorage.setItem("theme", action.payload); // Persist in local storage
    },
  },
});

export const { setTheme } = themeSlice.actions;
export default themeSlice.reducer;
