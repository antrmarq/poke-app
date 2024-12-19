import './bootstrap';
import React from 'react';
import ReactDOM from 'react-dom/client';
import MainBody from './components/MainBody';

ReactDOM.createRoot(document.getElementById('app')).render(
    <React.StrictMode>
        <MainBody />
    </React.StrictMode>
);
