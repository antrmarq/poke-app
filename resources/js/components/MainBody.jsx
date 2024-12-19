import React from 'react';
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import Home from './home/Home';
import About from './general/About';

const MainBody = () => {
    return (
        <Router>
            <div id="main-body" className="relative flex flex-grow flex-col justify-between">
                {/* Define routes */}
                <Routes>
                    <Route path="/" element={<div>Welcome to the Main Page</div>} />
                    <Route path="/home" element={<Home />} />
                    <Route path="/about" element={<About />} />
                    <Route path="*" element={<div>Page Not Found</div>} />
                </Routes>
            </div>
        </Router>
    );
};

export default MainBody;
