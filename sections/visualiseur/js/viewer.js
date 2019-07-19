"use strict";

/** A class that handles the machining program viewer */

class MachiningProgramViewer
{
    /**
	 * MachiningProgramViewer constructor.
     * 
     * @return {MachiningProgramViewer} This MachiningProgramViewer
	 */
	constructor()
	{
        if (!MachiningProgramViewer.instance) 
        {
            MachiningProgramViewer.instance = Object.seal(this.goToFirst());
        }
        return MachiningProgramViewer.instance;
	}

    /**
	 * Initializes the MachiningProgramViewer.
     * 
     * @return {MachiningProgramViewer} This MachiningProgramViewer
	 */
    initialize()
    {
        this.hideAllPannels();
        let element = document.getElementsByClassName("pannelContainer")[0];
        this.currentPannel = element ? element : null;
        return this;
    }

	/**
	 * Goes to the first pannel in the collection.
     * 
     * @return {MachiningProgramViewer} This MachiningProgramViewer
	 */
	goToFirst()
	{
        if(!(this.currentPannel instanceof Element) && !(this.initialize().currentPannel instanceof Element))
        {
            return this;
        }

        return this.goTo(this.currentPannel.parentElement.firstElementChild)
	}

	/**
	 * Goes to the last pannel in the collection.
     * 
     * @return {MachiningProgramViewer} This MachiningProgramViewer
	 */
	goToLast()
	{
        if(!(this.currentPannel instanceof Element) && !(this.initialize().currentPannel instanceof Element))
        {
            return this;
        }

		return this.goTo(this.currentPannel.parentElement.lastElementChild)
	}

	/**
	 * Goes to the previous pannel in the collection.
     * 
     * @return {MachiningProgramViewer} This MachiningProgramViewer
	 */
	goToPrevious()
	{
        if(!(this.currentPannel instanceof Element) && !(this.initialize().currentPannel instanceof Element))
        {
            return this;
        }

		return this.goTo(this.currentPannel.previousElementSibling);
	}

	/**
	 * Goes to the next pannel in the collection
     * 
     * @return {MachiningProgramViewer} This MachiningProgramViewer
	 */
	goToNext()
	{
        if(!(this.currentPannel instanceof Element) && !(this.initialize().currentPannel instanceof Element))
        {
            return this;
        }

        return this.goTo(this.currentPannel.nextElementSibling);
	}

	/**
	 * Goes to the specified index in the collection
	 * @param {Element} newPannel The new pannel to display 
     * 
     * @return {MachiningProgramViewer} This MachiningProgramViewer
	 */
	goTo(pannel)
	{
        if(!(this.currentPannel instanceof Element) && !(this.initialize().currentPannel instanceof Element))
        {
            return this;
        }

        if(!(pannel instanceof Element))
        {
            return this;
        }

        this.currentPannel = pannel;
        return this.hideAllPannels().showPannel();
	}

	/**
	 * Prints the current pannel
	 * @param {Element|null} pannel The pannel to hide.
     * 
     * @return {MachiningProgramViewer} This MachiningProgramViewer
	 */
	printPannel(pannel = null)
	{
        if(!(pannel instanceof Element) && !(this.currentPannel instanceof Element))
        {
            return this;
        }
        else if(!(pannel instanceof Element) && (this.currentPannel instanceof Element))
        {
            pannel = this.currentPannel;
        }

        /* Ensure the pannel on screen is the pannel to print. */
        if(pannel !== this.currentPannel)
        {
            this.hidePannel().showPannel(pannel);
        }

        window.print();

        /* Display the current pannel if the pannel on screen is not the current pannel. */
        if(pannel !== this.currentPannel)
        {
            this.hidePannel(pannel).showPannel();
        }
        
        return this;
	}

	/**
	 * Prints all pannels
	 * 
     * @return {MachiningProgramViewer} This MachiningProgramViewer
	 */
	printAllPannels()
	{
        /* Back up the display status of all the pannels and make them all temporarily visible. */
		let displayStatusArray = [...document.getElementsByClassName("pannelContainer")].map(function(element){
            let backedUpState = element.style.display;
            this.showPannel(element);
            return backedUpState;
        }, this);

        window.print();
        
        /* Restore the display status of all the pannels. */
		displayStatusArray.map(function(status, index){
            if(status === "none")
            {
                this.hidePannel(document.getElementsByClassName("pannelContainer")[index]);
            }
        }, this);
        return this;
    }
    
    /**
     * Shows the specified pannel
     * @param {Element|null} pannel The pannel to hide.
     * 
     * @return {MachiningProgramViewer} This MachiningProgramViewer
     */
    showPannel(pannel = null)
    {
        if(!(pannel instanceof Element) && !(this.currentPannel instanceof Element))
        {
            return this;
        }
        else if(!(pannel instanceof Element) && (this.currentPannel instanceof Element))
        {
            pannel = this.currentPannel;
        }

        pannel.style.display = null;
        return this;
    }

    /**
     * Hides the specified pannel
     * @param {Element|null} pannel The pannel to hide.
     * 
     * @return {MachiningProgramViewer} This MachiningProgramViewer
     */
    hidePannel(pannel = null)
    {
        if(!(pannel instanceof Element) && !(this.currentPannel instanceof Element))
        {
            return this;
        }
        else if(!(pannel instanceof Element) && (this.currentPannel instanceof Element))
        {
            pannel = this.currentPannel;
        }

        pannel.style.display = "none";
        return this;
    }

    /**
     * Shows all pannels (used for printing)
     * 
     * @return {MachiningProgramViewer} This MachiningProgramViewer
     */
    showAllPannels()
    {
        [...document.getElementsByClassName("pannelContainer")].map(function(pannel){
            this.showPannel(pannel);
        }, this);
        return this;
    }

    /**
     * Hides all pannels (used for printing)
     * 
     * @return {MachiningProgramViewer} This MachiningProgramViewer
     */
    hideAllPannels()
    {
        Array.from(document.getElementsByClassName("pannelContainer")).map(function(pannel){
            this.hidePannel(pannel);
        }, this);
        return this;
    }
}