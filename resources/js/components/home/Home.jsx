import React, { useState, useEffect } from 'react';
import axios from 'axios';

const Home = () => {
    const [query, setInputValue] = useState('');
    const [responseData, setResponseData] = useState(null);
    const [error, setError] = useState(null);
    const [suggestion, setSuggestion] = useState(null);
    const [typingTimeout, setTypingTimeout] = useState(null); // Timeout ID for debouncing

    // API Call Function
    const fetchPokemon = async (query) => {
        try {
            const response = await axios.get(`/api/pokemon/${query}`);
            console.log(response.data);
            setResponseData(response.data);
            setError(null); // Clear previous errors
            setSuggestion(null); // Clear any previous suggestion
        } catch (err) {
            const errorData = err.response?.data || {};
            setError(errorData.error || 'An error occurred');
            setResponseData(null);
            setSuggestion(errorData.suggestion || null); // Set suggestion if present
        }
    };

    // Handler for input change
    const handleInputChange = (e) => {
        const value = e.target.value;
        setInputValue(value);

        // If input is cleared, reset Pokémon data and errors
        if (value.trim() === '') {
            setResponseData(null);
            setError(null);
            setSuggestion(null);
            return; // Exit early
        }

        // Clear previous timeout
        if (typingTimeout) {
            clearTimeout(typingTimeout);
        }

        // Set a new timeout for debouncing
        setTypingTimeout(
            setTimeout(() => {
                if (
                    !responseData ||
                    (responseData.name.toLowerCase() !== value.toLowerCase() &&
                        responseData.id !== parseInt(value))
                ) {
                    fetchPokemon(value);
                }
            }, 500) // 0.5-second debounce
        );
    };

    // Handler for pressing Enter
    const handleKeyPress = async (e) => {
        if (e.key === 'Enter' && query.trim() !== '') {
            if (typingTimeout) {
                clearTimeout(typingTimeout); // Clear timeout to avoid double requests
            }

            if (
                !responseData ||
                (responseData.name.toLowerCase() !== query.toLowerCase() &&
                    responseData.id !== parseInt(query))
            ) {
                fetchPokemon(query);
            }
        }
    };

    // Clear input and data
    const clearPokemon = () => {
        setResponseData(null);
        setError(null);
        setSuggestion(null);
        setInputValue('');
    };

    // Fetch random Pokémon
    const fetchRandomPokemon = async () => {
        try {
            const response = await axios.get(`/api/pokemon/random`);
            const randomPokemon = response.data;

            const capitalizedName =
                randomPokemon.name.charAt(0).toUpperCase() +
                randomPokemon.name.slice(1);

            setResponseData(randomPokemon);
            setError(null); // Clear previous errors
            setSuggestion(null); // Clear any previous suggestion
            setInputValue(capitalizedName);
        } catch (err) {
            setError('Failed to fetch a random Pokémon. Please try again.');
        }
    };

    // Handle suggestion button click
    const handleSuggestionClick = () => {
        if (suggestion) {
            fetchPokemon(suggestion.name.toLowerCase());
            setInputValue(
                suggestion.name.charAt(0).toUpperCase() + suggestion.name.slice(1)
            );
        }
    };

    return (
        <div className="min-h-screen bg-gray-200 text-black flex flex-col">
            {/* Input field and buttons at the top */}
            <div className="text-center py-4 bg-white shadow-md">
                <div className="flex justify-center items-center space-x-2">
                    <input
                        type="text"
                        className="border border-gray-300 rounded-md px-4 py-2 w-[400px] text-center text-black"
                        placeholder="Enter Pokémon ID or Name"
                        value={query}
                        onChange={handleInputChange}
                        onKeyPress={handleKeyPress}
                    />
                    {query.trim() && (
                        <button
                            onClick={clearPokemon}
                            className="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600"
                        >
                            Clear
                        </button>
                    )}
                    <button
                        onClick={fetchRandomPokemon}
                        className="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
                    >
                        Random
                    </button>
                </div>
            </div>

            {/* Centered content */}
            <div className="flex-grow flex items-center justify-center">
                <div className="text-center">
                    {
                        !responseData && !error && (
                            <>
                                <h1 className="text-3xl font-bold">Welcome to the Home Page!</h1>
                                <p className="text-lg">Type a Pokémon ID or name to begin searching!</p>
                            </>
                        )
                    }
                    {/* Display fetched data */}
                    {responseData && (
                        <div className="mt-6">
                            <h2 className="text-2xl font-bold">Pokémon Information</h2>
                            <p><strong>Name:</strong> {responseData.name}</p>
                            <p><strong>ID:</strong> {responseData.id}</p>
                            <p><strong>Type 1:</strong> {responseData.type1}</p>
                            <p><strong>Type 2:</strong> {responseData.type2 || 'None'}</p>
                            <p><strong>Abilities:</strong></p>
                            <ul className="list-disc ml-5 inline-block text-left">
                                {JSON.parse(responseData.abilities).map((ability, index) => (
                                    <li
                                        className="capitalize"
                                        key={index}
                                    >
                                        {ability.name} (<a href={ability.url} target="_blank" rel="noopener noreferrer">Learn more</a>)
                                    </li>
                                ))}
                            </ul>
                            <img
                                src={responseData.sprite_url}
                                alt={`${responseData.name} sprite`}
                                className="mt-4 w-32 h-32 mx-auto"
                            />
                        </div>
                    )}

                    {/* Display error message */}
                    {error && (
                        <div className="mt-4">
                            <p className="text-red-500">{error}</p>
                            {suggestion && (
                                <button
                                    onClick={handleSuggestionClick}
                                    className="mt-2 px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600"
                                >
                                    Query Suggested Pokémon: {suggestion.name}
                                </button>
                            )}
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
};

export default Home;
