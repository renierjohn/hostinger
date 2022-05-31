import React from "react"
import ReactDom from "react-dom"

import AppMain from "./AppMain"
import AppProduct from "./AppProduct"

ReactDom.render(<AppMain />, document.getElementById('main'))
ReactDom.render(<AppProduct />, document.getElementById('product'))