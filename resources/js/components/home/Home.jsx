import React, { useState } from "react";
import axios from "axios";

const Home = () => {
    const [query, setInputValue] = useState("");
    const [responseData, setResponseData] = useState(null);
    const [error, setError] = useState(null);
    const [suggestion, setSuggestion] = useState(null);
    const [typingTimeout, setTypingTimeout] = useState(null);

    // API Call Function
    const fetchPokemon = async (query) => {
        try {
            const response = await axios.get(`/api/pokemon/${query}`);
            console.log(response.data);
            setResponseData(response.data);
            setError(null);
            setSuggestion(null);
        } catch (err) {
            const errorData = err.response?.data || {};
            setError(errorData.error || "An error occurred");
            setResponseData(null);
            setSuggestion(errorData.suggestion || null);
        }
    };

    // Handler for input change with debounce
    const handleInputChange = (e) => {
        const value = e.target.value;
        setInputValue(value);

        if (value.trim() === "") {
            setResponseData(null);
            setError(null);
            setSuggestion(null);
            return;
        }

        if (typingTimeout) {
            clearTimeout(typingTimeout);
        }

        setTypingTimeout(
            setTimeout(() => {
                fetchPokemon(value);
            }, 500)
        );
    };

    // Handler for pressing Enter
    const handleKeyPress = async (e) => {
        if (e.key === "Enter" && query.trim() !== "") {
            if (typingTimeout) {
                clearTimeout(typingTimeout);
            }
            fetchPokemon(query);
        }
    };

    // Clear input and data
    const clearPokemon = () => {
        setResponseData(null);
        setError(null);
        setSuggestion(null);
        setInputValue("");
    };

    // Fetch random Pokémon
    const fetchRandomPokemon = async () => {
        try {
            const response = await axios.get(`/api/pokemon/random`);
            setResponseData(response.data);
            setError(null);
            setSuggestion(null);
            setInputValue(response.data.name.charAt(0).toUpperCase() + response.data.name.slice(1));
        } catch (err) {
            setError("Failed to fetch a random Pokémon. Please try again.");
        }
    };

    // Handle suggestion button click
    const handleSuggestionClick = () => {
        if (suggestion) {
            fetchPokemon(suggestion.toLowerCase());
            setInputValue(suggestion.charAt(0).toUpperCase() + suggestion.slice(1));
        }
    };

    // Function to determine bar color based on stat value
    const getStatColor = (stat) => {
        if (stat >= 100) return "bg-green-500"; // High stat
        if (stat >= 70) return "bg-yellow-500"; // Medium stat
        return "bg-red-500"; // Low stat
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
                        <button onClick={clearPokemon} className="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">
                            Clear
                        </button>
                    )}
                    <button onClick={fetchRandomPokemon} className="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                        Random
                    </button>
                </div>
            </div>

            {/* Centered content */}
            <div className="flex-grow flex items-center justify-center">
                <div className="text-center">
                    {!responseData && !error && (
                        <>
                            <h1 className="text-3xl font-bold">Welcome to the Pokémon Database!</h1>
                            <p className="text-lg">Type a Pokémon ID or name to begin searching!</p>
                        </>
                    )}

                    {/* Display fetched data */}
                    {responseData && (
                        <div className="mt-6">
                            <h2 className="text-2xl font-bold">Pokémon Information</h2>
                            <p><strong>Name:</strong> {responseData.name}</p>
                            <p><strong>ID:</strong> {responseData.id}</p>
                            <p><strong>Genus:</strong> {responseData.genus}</p>
                            <p><strong>Generation:</strong> {responseData.generation}</p>
                            <p><strong>Height:</strong> {responseData.height} dm</p>
                            <p><strong>Weight:</strong> {responseData.weight} hg</p>

                            {/* Display Types */}
                            <p><strong>Types:</strong></p>
                            <ul className="list-disc ml-5 inline-block text-left">
                                {responseData.types.map((typeObj, index) => (
                                    <li className="capitalize" key={index}>
                                        {typeObj.type}
                                    </li>
                                ))}
                            </ul>

                            {/* Display Pokémon Stats */}
                            <p><strong>Base Stats:</strong></p>
                            <ul className="list-none ml-5 inline-block text-left w-64">
                                {responseData.stats && Object.entries(responseData.stats).map(([key, value], index) => (
                                    <li key={index} className="flex items-center">
                                        <span className="w-24 capitalize">{key}:</span>
                                        <div className="w-40 h-4 bg-gray-300 rounded-full overflow-hidden ml-2">
                                            <div className={`h-full ${getStatColor(value)}`} style={{ width: `${(value / 150) * 100}%` }}></div>
                                        </div>
                                        <span className="ml-2">{value}</span>
                                    </li>
                                ))}
                            </ul>

                            {/* Display Effectiveness */}
                            <p><strong>Effectiveness:</strong></p>
                            <ul className="list-disc ml-5 inline-block text-left">
                                {responseData.effectiveness && (
                                    <>
                                        <li><strong>Weaknesses (x2):</strong> {responseData.effectiveness.weaknesses.length > 0 ? responseData.effectiveness.weaknesses.join(", ") : "None"}</li>
                                        <li><strong>Resistances (x0.5):</strong> {responseData.effectiveness.resistances.length > 0 ? responseData.effectiveness.resistances.join(", ") : "None"}</li>
                                        <li><strong>Immunities:</strong> {responseData.effectiveness.immunities.length > 0 ? responseData.effectiveness.immunities.join(", ") : "None"}</li>
                                    </>
                                )}
                            </ul>

                            <img src={responseData.sprite_url} alt={`${responseData.name} sprite`} className="mt-4 w-32 h-32 mx-auto" />
                        </div>
                    )}

                    {/* Display error message */}
                    {error && (
                        <div className="mt-4">
                            <p className="text-red-500">{error}</p>
                            {suggestion && (
                                <button onClick={handleSuggestionClick} className="mt-2 px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600">
                                    Query Suggested Pokémon: {suggestion}
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
