const express = require("express");
const mongoose = require("mongoose");
const cors = require("cors");

const app = express();
app.use(cors());
app.use(express.json());


// Connections to MongoDB

mongoose.connect("mongodb://127.0.0.1:27017/artify").then(() => 
    console.log("MongoDB Connected")).catch(err => console.log("MongoDB error", err));

// Route tests
app.get("/", (req, res) => {
    res.send("Backend is running");
});

// Run Server
app.listen(8000, () => {
    console.log("Server is running on port 5000");
});